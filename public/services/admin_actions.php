<?php
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Acces neautorizat!");
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Eroare de securitate (CSRF)!");
}

$action = $_POST['action'] ?? '';

if ($action === 'adauga') {
    $check_title = $pdo->prepare("SELECT COUNT(*) from Carte where titlu = ?");
    $check_title->execute([$_POST['titlu']]);

    if ($check_title->fetchColumn() > 0) {
        $_SESSION['error'] = "Cartea " . $_POST['titlu'] . ' se afla deja in librarie!';
        header("Location: ../admin.php");
        exit();
    }

    $sql = "INSERT INTO Carte (titlu, id_autor, id_editura, an_publicare, stoc_total, stoc_disponibil) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['titlu'], $_POST['id_autor'], $_POST['id_editura'], (int)$_POST['an_publicare'], $_POST['stoc_total'], $_POST['stoc_total']]);
    $_SESSION['status_update'] = "Cartea " . $_POST['titlu'] . ' a fost adaugata cu succes!';

}

if ($action === 'modifica_stoc') {
    $stmt = $pdo->prepare("SELECT * FROM Carte WHERE id_carte = ?");
    $stmt->execute([$_POST['id_carte']]);
    $bookInfo = $stmt->fetch();

    $stmt = $pdo->prepare("UPDATE Carte SET stoc_disponibil = ? , stoc_total = ? WHERE id_carte = ?");
    $stmt->execute([$_POST['stoc_disponibil'], $bookInfo['stoc_total'] + $_POST['stoc_disponibil'] - $bookInfo['stoc_disponibil'], $_POST['id_carte'] ]);

    $_SESSION['status_update'] = "Stocul cartii ". $bookInfo['titlu'] . " a fost actualizat cu succes!";
}

if ($action === 'adauga_autor') {

    $check_name = $pdo->prepare("SELECT COUNT(*) FROM Autor WHERE nume = ?");
    $check_name->execute([$_POST['nume_autor']]);

    if (!$check_name->fetchColumn() > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Autor (nume, nationalitate) VALUES (? , ?)");
            $stmt->execute([$_POST['nume_autor'], $_POST['nationalitate']]);
            $_SESSION['status_update'] = "Autorul " . $_POST['nume_autor'] . " a fost adaugat cu succes.";

        } catch(PDOException $e) {
            $_SESSION['error'] = "A aparut o eroare neașteptată la baza de date.";
            header("Location: ../admin.php");
            exit();
        }
    }
        else {
            $_SESSION['error'] = "Autorul exista deja.";
            header("Location: ../admin.php");
            exit();
        }
}

header("Location: ../admin.php");
exit();