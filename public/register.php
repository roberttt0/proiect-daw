<?php require 'db.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Înregistrare</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
    <h2>Înregistrare</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nume = $_POST['nume'];
        $email = $_POST['email'];
        $pass = password_hash($_POST['parola'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO Utilizator (nume, email, parola) VALUES (?, ?, ?)");
        if ($stmt->execute([$nume, $email, $pass])) {
            echo "<div class='alert alert-success'>Cont creat! <a href='login.php'>Autentifică-te</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Eroare!</div>";
        }
    }
    ?>
    <form method="POST">
        <input type="text" name="nume" placeholder="Nume complet" class="form-control mb-2" required>
        <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
        <input type="password" name="parola" placeholder="Parola" class="form-control mb-2" required>
        <button type="submit" class="btn btn-primary">Creează cont</button>
    </form>
</body>
</html>