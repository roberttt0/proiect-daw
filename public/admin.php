<?php
require './services/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$autori = $pdo->query("SELECT id_autor, nume, nationalitate FROM Autor")->fetchAll();
$edituri = $pdo->query("SELECT id_editura, nume FROM Editura")->fetchAll();

$carti = $pdo->query("SELECT c.*, a.nume as autor, e.nume as editura 
                      FROM Carte c 
                      JOIN Autor a ON c.id_autor = a.id_autor 
                      JOIN Editura e ON c.id_editura = e.id_editura")->fetchAll();

?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Administrare Carti</title>
    <link rel="stylesheet" href="./resources/css/style.css">
</head>

<body>

    <?php
    require './components/header.php';
    ?>

    <div class="container">

        <?php if (isset($_SESSION['status_update'])): ?>
            <div style="color: white; background-color: green; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?php
                echo $_SESSION['status_update'];
                unset($_SESSION['status_update']);
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: white; background-color: #ff4d4d; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <h1>Panou Administrare - Gestionare Carti</h1>
        <hr style="margin: 40px 0px">

        <h3>Adauga o carte noua</h3>
        <form action="./services/admin_actions.php" method="POST" style="display: flex; gap: 20px;">
            <input type="hidden" name="action" value="adauga">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="titlu" placeholder="Titlu Carte" required>

            <select name="id_autor" required>
                <option value="">Alege Autor</option>
                <?php foreach ($autori as $a): ?>
                    <option value="<?= $a['id_autor'] ?>"><?= htmlspecialchars($a['nume']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="id_editura" required>
                <option value="">Alege Editura</option>
                <?php foreach ($edituri as $e): ?>
                    <option value="<?= $e['id_editura'] ?>"><?= htmlspecialchars($e['nume']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="an_publicare" placeholder="An" required>
            <input type="number" name="stoc_total" placeholder="Stoc Total" required min="0" max="20">
            <button type="submit" class="btn btn-primary">Adauga Carte</button>
        </form>

        <hr style="margin: 40px 0px">


        <h3>Inventar Carti</h3>

        <table border="1">
            <tr>
                <th>Titlu</th>
                <th>Autor</th>
                <th>Editura</th>
                <th>Stoc (D/T)</th>
                <th>Actiuni</th>
            </tr>
            <?php foreach ($carti as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['titlu']) ?></td>
                    <td><?= htmlspecialchars($c['autor']) ?></td>
                    <td><?= htmlspecialchars($c['editura']) ?></td>
                    <td><?= $c['stoc_disponibil'] ?> / <?= $c['stoc_total'] ?></td>
                    <td>
                        <form action="./services/admin_actions.php" method="POST" style="display:flex; gap: 10px;">
                            <input type="hidden" name="action" value="modifica_stoc">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <input type="hidden" name="id_carte" value="<?= $c['id_carte'] ?>">
                            <input type="number" name="stoc_disponibil" value="<?= $c['stoc_disponibil'] ?>" min="0" max="20">
                            <button type="submit" class="btn" style="background-color: black">Modifica stoc disponibil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Autori</h3>

        <form action="./services/admin_actions.php" method="POST">
            <input type="hidden" name="action" value="adauga_autor">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label>Nume autor</label>
                <input type="text" name="nume_autor" required>
            </div>

            <div class="form-group">
                <label>Nationalitate autor</label>
                <input type="text" name="nationalitate" required>
            </div>

            <button
                type="submit"
                class="btn">Adauga autor</button>
        </form>

    </div>

    <?php
    require './components/footer.php';
    ?>
</body>

</html>