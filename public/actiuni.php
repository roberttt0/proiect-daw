<?php
require 'db.php';

if (!isset($_SESSION['id_utilizator']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php"); exit;
}

$id_user = $_SESSION['id_utilizator'];
$actiune = $_POST['actiune'];

if ($actiune == 'imprumuta') {
    $id_carte = $_POST['id_carte'];
    
    $stmt = $pdo->prepare("SELECT stoc_disponibil FROM Carte WHERE id_carte = ?");
    $stmt->execute([$id_carte]);
    $carte = $stmt->fetch();

    if ($carte && $carte['stoc_disponibil'] > 0) {
        $pdo->prepare("UPDATE Carte SET stoc_disponibil = stoc_disponibil - 1 WHERE id_carte = ?")->execute([$id_carte]);
        
        $data_imprumut = date('Y-m-d');
        $data_scadenta = date('Y-m-d', strtotime('+14 days'));
        
        $stmt_ins = $pdo->prepare("INSERT INTO Imprumut (id_utilizator, id_carte, data_imprumut, data_scadenta) VALUES (?, ?, ?, ?)");
        $stmt_ins->execute([$id_user, $id_carte, $data_imprumut, $data_scadenta]);
    }
} 
elseif ($actiune == 'returneaza') {
    $id_imprumut = $_POST['id_imprumut'];
    $id_carte = $_POST['id_carte'];

    $pdo->prepare("UPDATE Imprumut SET data_retur = NOW() WHERE id_imprumut = ?")->execute([$id_imprumut]);

    $pdo->prepare("UPDATE Carte SET stoc_disponibil = stoc_disponibil + 1 WHERE id_carte = ?")->execute([$id_carte]);
}

header("Location: index.php");
exit;
?>