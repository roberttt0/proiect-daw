<?php 
require 'db.php'; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['parola'];

    $stmt = $pdo->prepare("SELECT * FROM Utilizator WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['parola'])) {
        $_SESSION['id_utilizator'] = $user['id_utilizator'];
        $_SESSION['nume'] = $user['nume'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Email sau parolă incorectă!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
    <h2>Autentificare</h2>
    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
        <input type="password" name="parola" placeholder="Parola" class="form-control mb-2" required>
        <button type="submit" class="btn btn-success">Intră în cont</button>
        <a href="register.php" class="btn btn-link">Nu ai cont?</a>
    </form>
</body>
</html>