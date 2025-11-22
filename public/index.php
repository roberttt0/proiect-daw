<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Biblioteca Online</title>
    <link rel="stylesheet" href="./resources/css/style.css">
</head>

<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Biblioteca</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Acasa</a></li>
                    <li><a href="about.php">Despre</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">Profilul Meu</a></li>
                        <li><a href="action.php?action=logout">Logout (<?= $_SESSION['nume'] ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Autentificare</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
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
</body>

</html>