<?php
require 'db.php';
if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id_carte = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comentariu'])) {
    $stmt = $pdo->prepare("INSERT INTO Recenzie (id_carte, id_utilizator, rating, comentariu) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_carte, $_SESSION['id_utilizator'], $_POST['rating'], $_POST['comentariu']]);
}

$stmt = $pdo->prepare("SELECT * FROM Carte WHERE id_carte = ?");
$stmt->execute([$id_carte]);
$carte = $stmt->fetch();

$stmt_rec = $pdo->prepare("SELECT Recenzie.*, Utilizator.nume FROM Recenzie JOIN Utilizator ON Recenzie.id_utilizator = Utilizator.id_utilizator WHERE id_carte = ? ORDER BY data DESC");
$stmt_rec->execute([$id_carte]);
$recenzii = $stmt_rec->fetchAll();
?>

<!DOCTYPE html>
<html>
<head><title><?= htmlspecialchars($carte['titlu']) ?></title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-4">
    <a href="index.php" class="btn btn-outline-secondary mb-3">&larr; Înapoi</a>
    
    <div class="row">
        <div class="col-md-4">
            <img src="<?= $carte['coperta'] ?>" class="img-fluid rounded">
        </div>
        <div class="col-md-8">
            <h2><?= htmlspecialchars($carte['titlu']) ?></h2>
            <p>Categorie: <?= htmlspecialchars($carte['categorie']) ?></p>
            <hr>
            
            <h4>Recenzii</h4>
            <?php if(isset($_SESSION['id_utilizator'])): ?>
            <form method="POST" class="mb-4 card p-3 bg-light">
                <h6>Lasă o recenzie:</h6>
                <select name="rating" class="form-select mb-2">
                    <option value="5">5 Stele - Excelent</option>
                    <option value="4">4 Stele - Bun</option>
                    <option value="3">3 Stele - Mediu</option>
                    <option value="2">2 Stele - Slab</option>
                    <option value="1">1 Stea - Groaznic</option>
                </select>
                <textarea name="comentariu" class="form-control mb-2" placeholder="Opinia ta..." required></textarea>
                <button type="submit" class="btn btn-primary btn-sm">Postează</button>
            </form>
            <?php endif; ?>

            <?php foreach ($recenzii as $rec): ?>
                <div class="border-bottom mb-2">
                    <strong><?= htmlspecialchars($rec['nume']) ?></strong> 
                    <span class="text-warning"><?= str_repeat('★', $rec['rating']) ?></span>
                    <p><?= htmlspecialchars($rec['comentariu']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>