<?php require './services/config.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Biblioteca Online</title>
    <link rel="stylesheet" href="./resources/css/style.css">
</head>

<body>
    <?php
    require './components/header.php';
    ?>

    <div class="container">

        <h2>Statistica Bibliotecii</h2>
        <img src="./grafic.php" alt="Grafic Statistica">

        <h2>Carti Disponibile</h2>
        <?php
        $stmt = $pdo->query("SELECT c.*, a.nume as autor_nume FROM Carte c JOIN Autor a ON c.id_autor = a.id_autor");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <div class="card">
                <h3><?= htmlspecialchars($row['titlu']) ?></h3>
                <p><strong>Autor:</strong> <?= htmlspecialchars($row['autor_nume']) ?></p>
                <p><strong>Stoc Disponibil:</strong> <?= $row['stoc_disponibil'] ?> / <?= $row['stoc_total'] ?></p>
                <a href="book.php?id=<?= $row['id_carte'] ?>" class="btn btn-secondary">Vezi Detalii & imprumuta</a>
            </div>
        <?php endwhile; ?>
    </div>

    <?php
    require './components/footer.php';
    ?>
</body>

</html>