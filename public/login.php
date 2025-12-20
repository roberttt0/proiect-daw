<?php
require './services/config.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="./resources/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js"></script>
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh">
    <header>
        <div class="container">
            <div id="branding">
                <h1>Biblioteca</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">inapoi la Site</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container auth-wrapper">
        <div class="auth-box">
            <h2>Bine ai venit!</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="color: white; background-color: #ff4d4d; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="./services/action.php" method="POST">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="exemplu@email.com" required>
                </div>

                <div class="form-group">
                    <label>Parola</label>
                    <input type="password" name="parola" placeholder="••••••••" required>
                </div>

                <br>
                <!-- div to show reCAPTCHA -->
                <div class="g-recaptcha"
                    data-sitekey="6LcskzAsAAAAAHyiPRufn6UmVsp86TIg12vXYmcK">
                </div>
                <br>

                <button
                    type="submit"
                    class="btn btn-block">Intra in cont</button>
            </form>

            <div class="auth-footer">
                Nu ai cont? <a href="register.php">Inregistreaza-te aici</a>
            </div>
        </div>
    </div>
    <?php
    require './components/footer.php';
    ?>
</body>

</html>