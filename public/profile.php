<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) redirect('login.php');

$id_user = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT i.*, c.titlu FROM Imprumut i 
                       JOIN Carte c ON i.id_carte = c.id_carte 
                       WHERE i.id_utilizator = ? AND i.data_retur IS NULL");
$stmt->execute([$id_user]);
$imprumuturi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Profilul Meu</title>
    <link rel="stylesheet" href="./resources/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Salut, <?= htmlspecialchars($_SESSION['nume']) ?>!</h2>
        <a href="index.php" class="btn btn-secondary">Inapoi la Carti</a>

        <h3>Cartile mele imprumutate</h3>
        <?php if (count($imprumuturi) > 0): ?>
            <table>
                <tr>
                    <th>Carte</th>
                    <th>Data imprumut</th>
                    <th>Scadenta</th>
                    <th>Actiune</th>
                </tr>
                <?php foreach ($imprumuturi as $imp): ?>
                    <tr>
                        <td><?= htmlspecialchars($imp['titlu']) ?></td>
                        <td><?= $imp['data_imprumut'] ?></td>
                        <td><?= $imp['data_scadenta'] ?></td>
                        <td>
                            <form action="action.php" method="POST">
                                <input type="hidden" name="action" value="returneaza">
                                <input type="hidden" name="id_imprumut" value="<?= $imp['id_imprumut'] ?>">
                                <input type="hidden" name="id_carte" value="<?= $imp['id_carte'] ?>">
                                <button type="submit" class="btn btn-danger">Returneaza</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Nu ai niciun imprumut activ.</p>
        <?php endif; ?>
    </div>
</body>

</html>