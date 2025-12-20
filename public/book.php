<?php
require './services/config.php';

$id_carte = $_GET['id'] ?? 0;
$id_user = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT c.*, a.nume as autor, e.nume as editura FROM Carte c 
                       JOIN Autor a ON c.id_autor = a.id_autor 
                       JOIN Editura e ON c.id_editura = e.id_editura 
                       WHERE id_carte = ?");
$stmt->execute([$id_carte]);
$carte = $stmt->fetch();

if (!$carte) die("Cartea nu exista sau a fost stearsa.");

$deja_imprumutata = false;
if ($id_user > 0) {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Imprumut WHERE id_utilizator = ? AND id_carte = ? AND data_retur IS NULL");
    $checkStmt->execute([$id_user, $id_carte]);
    if ($checkStmt->fetchColumn() > 0) {
        $deja_imprumutata = true;
    }
}

$revStmt = $pdo->prepare("SELECT r.*, u.nume, u.prenume FROM Recenzie r 
                          JOIN Utilizator u ON r.id_utilizator = u.id_utilizator 
                          WHERE id_carte = ? ORDER BY data DESC");
$revStmt->execute([$id_carte]);
$recenzii = $revStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($carte['titlu']) ?></title>
    <link rel="stylesheet" href="./resources/css/style.css">
</head>

<body>

    <?php
    require './components/header.php';
    ?>

    <div class="container">

        <div class="card">
            <h1><?= htmlspecialchars($carte['titlu']) ?></h1>
            <p><strong>Autor:</strong> <?= htmlspecialchars($carte['autor']) ?></p>
            <p><strong>Editura:</strong> <?= htmlspecialchars($carte['editura']) ?> (<?= $carte['an_publicare'] ?>)</p>
            <p><strong>Stoc disponibil:</strong> <?= $carte['stoc_disponibil'] ?> / <?= $carte['stoc_total'] ?></p>
            <hr>

            <?php if ($id_user > 0): ?>

                <?php if ($deja_imprumutata): ?>
                    <div class="alert" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">
                        <strong>Info:</strong> Ai deja aceasta carte imprumutata si nu ai returnat-o inca.
                        <br><a href="profile.php" style="color: #0c5460; font-weight: bold;">Mergi la profil pentru retur.</a>
                    </div>

                <?php elseif ($carte['stoc_disponibil'] > 0): ?>
                    <form action="./services/action.php" method="POST">
                        <input type="hidden" name="action" value="imprumuta">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <input type="hidden" name="id_carte" value="<?= $carte['id_carte'] ?>">
                        <button type="submit" class="btn">imprumuta Cartea</button>
                    </form>

                <?php else: ?>
                    <div class="alert" style="background: #f8d7da; color: #721c24;">
                        Stoc epuizat momentan. Revino mai tarziu.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p>Trebuie sa te <a href="login.php" style="color: #e8491d; font-weight: bold;">autentifici</a> pentru a imprumuta cartea.</p>
            <?php endif; ?>
        </div>

        <h3>Recenzii (<?= count($recenzii) ?>)</h3>

        <?php if (count($recenzii) == 0): ?>
            <p>Nu exista recenzii pentru aceasta carte. Fii primul care scrie!</p>
        <?php endif; ?>

        <?php foreach ($recenzii as $rev): ?>
            <div class="card" style="padding: 15px; background: #fdfdfd; border-left: 4px solid #e8491d;">
                <div style="display: flex; justify-content: space-between;">
                    <strong><?= htmlspecialchars($rev['nume'] . ' ' . $rev['prenume']) ?></strong>
                    <span style="color: #f39c12; font-weight: bold;">Rating: <?= $rev['rating'] ?>/5</span>
                </div>
                <p style="margin: 10px 0;"><?= nl2br(htmlspecialchars($rev['comentariu'])) ?></p>

                <small style="color: #888;" class="local-date" data-utc="<?= $rev['data'] ?>">
                    <?= $rev['data'] ?>
                </small>
            </div>
        <?php endforeach; ?>

        <?php if ($id_user > 0): ?>
            <div class="card" style="margin-top: 30px;">
                <h4>Lasa o recenzie</h4>
                <form action="./services/action.php" method="POST">
                    <input type="hidden" name="action" value="recenzie">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <input type="hidden" name="id_carte" value="<?= $carte['id_carte'] ?>">

                    <div class="form-group">
                        <label>Rating:</label>
                        <select name="rating" style="padding: 8px; width: 160px;" required>
                            <option value="5">5 - Excelent</option>
                            <option value="4">4 - Foarte bun</option>
                            <option value="3">3 - Bun</option>
                            <option value="2">2 - Slab</option>
                            <option value="1">1 - Foarte slab</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Comentariul tau:</label>
                        <textarea name="comentariu" rows="3" required placeholder="Ce ti-a placut la aceasta carte?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-secondary">Publica Recenzia</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; background: #eee;">
                <p><a href="login.php">Logheaza-te</a> pentru a lasa o recenzie.</p>
            </div>
        <?php endif; ?>

    </div>

    <?php
    require './components/footer.php';
    ?>
</body>

</html>