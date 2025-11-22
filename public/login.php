<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Login</title><link rel="stylesheet" href="./resources/css/style.css"></head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1>Biblioteca</h1></div>
            <nav><ul><li><a href="index.php">inapoi la Site</a></li></ul></nav>
        </div>
    </header>

    <div class="container auth-wrapper">
        <div class="auth-box">
            <h2>Bine ai venit!</h2>
            <form action="action.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="exemplu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label>Parola</label>
                    <input type="password" name="parola" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-block">Intra in cont</button>
            </form>
            
            <div class="auth-footer">
                Nu ai cont? <a href="register.php">Inregistreaza-te aici</a>
            </div>
        </div>
    </div>
</body>
</html>