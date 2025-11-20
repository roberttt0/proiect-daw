<?php 
require 'db.php'; 
if (!isset($_SESSION['id_utilizator'])) { header("Location: login.php"); exit; }

$id_user = $_SESSION['id_utilizator'];

$stmt = $pdo->query("SELECT Carte.*, Autor.nume as autor_nume FROM Carte LEFT JOIN Autor ON Carte.id_autor = Autor.id_autor");
$carti = $stmt->fetchAll();

$stmt_imprumuturi = $pdo->prepare("
    SELECT Imprumut.*, Carte.titlu 
    FROM Imprumut 
    JOIN Carte ON Imprumut.id_carte = Carte.id_carte 
    WHERE Imprumut.id_utilizator = ? AND Imprumut.data_retur IS NULL
");
$stmt_imprumuturi->execute([$id_user]);
$imprumuturi = $stmt_imprumuturi->fetchAll();
?>

<!DOCTYPE html>
<html>
<head><title>Biblioteca</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Salut, <?= htmlspecialchars($_SESSION['nume']) ?>!</h1>
        <a href="logout.php" class="btn btn-danger">Deconectare</a>
    </div>

    <?php if (count($imprumuturi) > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-warning">Cărți de returnat</div>
        <div class="card-body">
            <ul>
                <?php foreach ($imprumuturi as $imp): ?>
                    <li>
                        <strong><?= htmlspecialchars($imp['titlu']) ?></strong> 
                        (Scadență: <?= $imp['data_scadenta'] ?>)
                        <form action="actiuni.php" method="POST" class="d-inline">
                            <input type="hidden" name="actiune" value="returneaza">
                            <input type="hidden" name="id_imprumut" value="<?= $imp['id_imprumut'] ?>">
                            <input type="hidden" name="id_carte" value="<?= $imp['id_carte'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-dark">Returnează</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <h3>Cărți Disponibile</h3>
    <div class="row">
        <?php foreach ($carti as $carte): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <img src="<?= $carte['coperta'] ?>" class="card-img-top" alt="coperta" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($carte['titlu']) ?></h5>
                        <p class="card-text">Autor: <?= htmlspecialchars($carte['autor_nume']) ?></p>
                        <p class="text-muted">Stoc: <?= $carte['stoc_disponibil'] ?></p>
                        
                        <div class="d-flex gap-2">
                            <a href="carte.php?id=<?= $carte['id_carte'] ?>" class="btn btn-info btn-sm">Detalii & Recenzii</a>
                            
                            <?php if ($carte['stoc_disponibil'] > 0): ?>
                                <form action="actiuni.php" method="POST">
                                    <input type="hidden" name="actiune" value="imprumuta">
                                    <input type="hidden" name="id_carte" value="<?= $carte['id_carte'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Împrumută</button>
                                </form>
                            <?php else: ?>
                                <button disabled class="btn btn-secondary btn-sm">Indisponibil</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>