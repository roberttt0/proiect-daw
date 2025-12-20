<?php
require 'config.php';

$action = $_REQUEST['action'] ?? '';

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Eroare de securitate (CSRF)!");
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

    $secret_key = '6LcskzAsAAAAAG6P-Drq23QnIiCpcMzSvdJLPN6o';
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

        header("Location: ../index.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "A aparut o eroare neașteptată la baza de date.";
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

    $secret_key = '6LcskzAsAAAAAG6P-Drq23QnIiCpcMzSvdJLPN6o';
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
    // redirect('../index.php');
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
    header("Location: ../profile.php");
    exit();

}
