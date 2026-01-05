<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../libs/PHPMailer/Exception.php';
require __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require __DIR__ . '/../libs/PHPMailer/SMTP.php';

require 'config.php';
require '../env.php';

$action = $_REQUEST['action'] ?? '';

$token_primit = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

if (empty($token_primit) || $token_primit !== $_SESSION['csrf_token']) {
    die("Eroare de securitate (CSRF)! Token invalid sau expirat.");
}

if ($action == 'register') {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $email = $_POST['email'];
    $parola = password_hash($_POST['parola'], PASSWORD_DEFAULT);
    $ip = $_SERVER['REMOTE_ADDR'];
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $limit_minute = 15;
    $max_attempts = 500;

    if (empty($recaptchaResponse)) {
        $_SESSION['error'] = "Te rugam sa bifezi casuta reCAPTCHA";
        header("Location: ../register.php");
        exit();
    }

    $check_attempts = $pdo->prepare("
        SELECT COUNT(*) FROM register_attempts 
        WHERE (ip_address = ?) 
        AND attempt_time > NOW() - INTERVAL ? MINUTE
    ");
    $check_attempts->execute([$ip, $limit_minute]);
    $attempts_count = $check_attempts->fetchColumn();

    if ($attempts_count >= $max_attempts) {
        $_SESSION['error'] = "Prea multe incercari. Reîncearca in 15 minute.";
        header("Location: ../register.php");
        exit();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret'   => $secret_key,
        'response' => $recaptchaResponse,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = json_decode(curl_exec($ch));

    if (!$result->success) {
        $_SESSION['error'] = "Verificarea reCAPTCHA a esuat.";
        header("Location: ../register.php");
        exit();
    }

    $check_email = $pdo->prepare("SELECT COUNT(*) FROM Utilizator WHERE email = ?");
    $check_email->execute([$email]);

    if ($check_email->fetchColumn() > 0) {
        $_SESSION['error'] = "Acest email este deja inregistrat.";
        header("Location: ../register.php");
        exit();
    }

    try {
        $sql = "INSERT INTO Utilizator (nume, prenume, email, parola) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nume, $prenume, $email, $parola]);

        $id = $pdo->lastInsertId();

        session_regenerate_id();
        $_SESSION['user_id'] = $id;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'user';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $smtp_port;

            $mail->setFrom($smtp_user, 'Biblioteca Online');
            $mail->addAddress($email);

            $nume_complet = $nume . " " . $prenume;
            $site_url = "https://rantohi.daw.ssmr.ro";

            $html_body = "
<div style='background-color: #f4f7f6; padding: 30px; font-family: Arial, sans-serif;'>
    <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
        
        <div style='background-color: #2c3e50; padding: 25px; text-align: center;'>
            <h1 style='color: #ffffff; margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;'>Biblioteca Online</h1>
        </div>

        <div style='padding: 30px; color: #333333; line-height: 1.6;'>
            <h2 style='color: #2c3e50; margin-top: 0;'>Salut, $nume_complet!</h2>
            <p>Contul tau a fost creat cu succes pe platforma <strong>rantohi.daw.ssmr.ro</strong>.</p>
            
            <p>Acum ai acces la intregul nostru catalog de carti. Poti sa iti gestionezi imprumuturile, sa vizualizezi recenziile si sa vezi topul cartilor din aceasta saptamana.</p>

            <div style='text-align: center; margin: 35px 0;'>
                <a href='$site_url' 
                   style='background-color: #3498db; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                   Intra in contul tau
                </a>
            </div>

            <p style='font-size: 14px; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 20px;'>
                Daca nu ai solicitat crearea acestui cont, te rugam sa ignori acest mesaj.
            </p>
        </div>

        <div style='background-color: #f9f9f9; padding: 20px; text-align: center;'>
            <p style='font-size: 11px; color: #bdc3c7; margin: 0;'>
                © " . date('Y') . " rantohi.daw.ssmr.ro. Acest email a fost generat automat.
            </p>
        </div>
    </div>
</div>
";

            $mail->isHTML(true);
            $mail->Subject = 'Confirmare Creare Cont';
            $mail->Body    = $html_body;

            $mail->send();
        } catch (Exception $e) {
            header("Location: ../index.php");
        }

        header("Location: ../index.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "A aparut o eroare neasteptată la baza de date.";
        header("Location: ../register.php");
        exit();
    }
}

if ($action == 'login') {
    $email = $_POST['email'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $limit_minute = 15;
    $max_attempts = 5;

    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? 'nu_exista_cheia';

    if (empty($recaptchaResponse)) {
        $_SESSION['error'] = "Te rugam sa bifezi casuta reCAPTCHA";
        header("Location: ../login.php");
        exit();
    }

    $check_attempts = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts 
        WHERE (email = ? OR ip_address = ?) 
        AND attempt_time > NOW() - INTERVAL ? MINUTE
    ");
    $check_attempts->execute([$email, $ip, $limit_minute]);
    $attempts_count = $check_attempts->fetchColumn();

    if ($attempts_count >= $max_attempts) {
        $_SESSION['error'] = "Prea multe incercari. Reîncearca in 15 minute.";
        header("Location: ../login.php");
        exit();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret'   => $secret_key,
        'response' => $recaptchaResponse,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = json_decode(curl_exec($ch));

    if (!$result->success) {
        $_SESSION['error'] = "Verificarea reCAPTCHA a esuat.";
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM Utilizator WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['parola'], $user['parola'])) {
        $clear = $pdo->prepare("DELETE FROM login_attempts WHERE email = ? OR ip_address = ?");
        $clear->execute([$email, $ip]);

        session_regenerate_id();
        $_SESSION['user_id'] = $user['id_utilizator'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['rol'];

        redirect('../index.php');
    } else {
        $log_attempt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
        $log_attempt->execute([$email, $ip]);

        $_SESSION['error'] = "Email sau parola incorectă.";
        header("Location: ../login.php");
        exit();
    }
}

if ($action == 'logout') {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) redirect('../login.php');

if ($action == 'imprumuta') {
    $id_carte = $_POST['id_carte'];
    $id_user = $_SESSION['user_id'];

    $check = $pdo->prepare("SELECT COUNT(*) FROM Imprumut WHERE id_utilizator = ? AND id_carte = ? AND data_retur IS NULL");
    $check->execute([$id_user, $id_carte]);
    if ($check->fetchColumn() > 0) {
        die("Eroare: Ai deja aceasta carte imprumutata! <a href='profile.php'>Mergi la profil</a>");
    }

    $stmt = $pdo->prepare("SELECT stoc_disponibil FROM Carte WHERE id_carte = ?");
    $stmt->execute([$id_carte]);
    $carte = $stmt->fetch();

    if ($carte['stoc_disponibil'] > 0) {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE Carte SET stoc_disponibil = stoc_disponibil - 1 WHERE id_carte = ?")->execute([$id_carte]);
        $sql = "INSERT INTO Imprumut (id_utilizator, id_carte, data_imprumut, data_scadenta) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY))";
        $pdo->prepare($sql)->execute([$id_user, $id_carte]);
        $pdo->commit();
        redirect("../profile.php");
    } else {
        die("Stoc epuizat.");
    }
}

if ($action == 'returneaza') {
    $id_imprumut = $_POST['id_imprumut'];
    $id_carte = $_POST['id_carte'];

    $stmt = $pdo->prepare("SELECT titlu from Carte WHERE id_carte = ?");
    $stmt->execute([$id_carte]);
    $nume_carte = $stmt->fetchColumn();

    $pdo->beginTransaction();

    $pdo->prepare("UPDATE Imprumut SET data_retur = NOW() WHERE id_imprumut = ?")->execute([$id_imprumut]);

    $pdo->prepare("UPDATE Carte SET stoc_disponibil = stoc_disponibil + 1 WHERE id_carte = ?")->execute([$id_carte]);

    $pdo->commit();

    $_SESSION['confirm'] = 'Cartea ' . $nume_carte . " a fost returnata cu succes!";
    redirect("../profile.php");
}

if ($action == 'recenzie') {
    $id_carte = $_POST['id_carte'];
    $rating = $_POST['rating'];
    $comentariu = $_POST['comentariu'];
    $id_user = $_SESSION['user_id'];

    $data_curenta = date('Y-m-d H:i:s');

    $sql = "INSERT INTO Recenzie (id_carte, id_utilizator, rating, comentariu, data) VALUES (?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$id_carte, $id_user, $rating, $comentariu, $data_curenta]);

    redirect("../book.php?id=$id_carte");
}

if ($action == 'update_profile') {
    $user_id = $_SESSION['user_id'];
    $firstName = $_POST['prenume'];
    $lastName = $_POST['nume'];

    $stmt = "UPDATE Utilizator
    SET nume = ? , prenume = ?
    WHERE id_utilizator = ?
    ";

    $pdo->prepare($stmt)->execute([$lastName, $firstName, $user_id]);

    $_SESSION['confirm'] = 'Datele contului tau au fost actualizate cu succes!';
    header("Location: ../profile.php");
    exit();
}
