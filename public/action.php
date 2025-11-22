<?php
require 'config.php';

$action = $_REQUEST['action'] ?? '';

if ($action == 'register') {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $email = $_POST['email'];
    $parola = password_hash($_POST['parola'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO Utilizator (nume, prenume, email, parola) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$nume, $prenume, $email, $parola]);
        redirect('login.php');
    } catch (Exception $e) {
        die("Eroare la inregistrare (posibil email existent).");
    }
}

if ($action == 'login') {
    $email = $_POST['email'];
    $parola = $_POST['parola'];

    $stmt = $pdo->prepare("SELECT * FROM Utilizator WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($parola, $user['parola'])) {
        $_SESSION['user_id'] = $user['id_utilizator'];
        $_SESSION['nume'] = $user['nume'];
        redirect('index.php');
    } else {
        die("Email sau parola incorecta. <a href='login.php'>Incearca din nou</a>");
    }
}

if ($action == 'logout') {
    session_destroy();
    redirect('index.php');
}

if (!isset($_SESSION['user_id'])) redirect('login.php');

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
        redirect("profile.php");
    } else {
        die("Stoc epuizat.");
    }
}

if ($action == 'returneaza') {
    $id_imprumut = $_POST['id_imprumut'];
    $id_carte = $_POST['id_carte'];

    $pdo->beginTransaction();

    $pdo->prepare("UPDATE Imprumut SET data_retur = NOW() WHERE id_imprumut = ?")->execute([$id_imprumut]);

    $pdo->prepare("UPDATE Carte SET stoc_disponibil = stoc_disponibil + 1 WHERE id_carte = ?")->execute([$id_carte]);

    $pdo->commit();
    redirect("profile.php");
}

if ($action == 'recenzie') {
    $id_carte = $_POST['id_carte'];
    $rating = $_POST['rating'];
    $comentariu = $_POST['comentariu'];
    $id_user = $_SESSION['user_id'];

    $data_curenta = date('Y-m-d H:i:s');

    $sql = "INSERT INTO Recenzie (id_carte, id_utilizator, rating, comentariu, data) VALUES (?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$id_carte, $id_user, $rating, $comentariu, $data_curenta]);

    redirect("book.php?id=$id_carte");
}
