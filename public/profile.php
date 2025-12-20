<?php
require './services/config.php';
if (!isset($_SESSION['user_id'])) redirect('login.php');

$id_user = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT i.*, c.titlu FROM Imprumut i 
                       JOIN Carte c ON i.id_carte = c.id_carte 
                       WHERE i.id_utilizator = ? AND i.data_retur IS NULL");
$stmt->execute([$id_user]);
$imprumuturi = $stmt->fetchAll();

$stmt1 = $pdo->prepare("SELECT * from Utilizator WHERE (id_utilizator = ?)");
$stmt1->execute([$id_user]);
$utilizator = $stmt1->fetch();

?>
<!DOCTYPE html>
<html>

<head>
    <title>Profilul Meu</title>
    <link rel="stylesheet" href="./resources/css/style.css">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh">

    <?php
    require './components/header.php';
    ?>

    <div class="container">

        <?php if (isset($_SESSION['confirm'])): ?>
            <div style="color: white; background-color: green; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?php
                echo $_SESSION['confirm'];
                unset($_SESSION['confirm']);
                ?>
            </div>
        <?php endif; ?>

        <?php
        $nume_complet = htmlspecialchars($utilizator['nume'] . ' ' . $utilizator['prenume']);
        ?>
        <h2>Salut, <?= $nume_complet ?> !</h2>
        <a href="index.php" class="btn btn-secondary">Inapoi la Carti</a>

        <section class="edit-profile">
            <h3>Modifică datele personale</h3>
            <form action="./services/action.php" method="POST" class="form-edit">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="nume">Nume:</label>
                    <input type="text" name="nume" id="nume" value="<?= htmlspecialchars($utilizator['nume']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="prenume">Prenume:</label>
                    <input type="text" name="prenume" id="prenume" value="<?= htmlspecialchars($utilizator['prenume']) ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Salvează modificările</button>
            </form>
        </section>

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
                            <form action="./services/action.php" method="POST">
                                <input type="hidden" name="action" value="returneaza">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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

    <?php
    require './components/footer.php';
    ?>
</body>

</html>