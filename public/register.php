<!DOCTYPE html>
<html>
<head><title>Register</title><link rel="stylesheet" href="./resources/css/style.css"></head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1>Biblioteca</h1></div>
            <nav><ul><li><a href="index.php">inapoi la Site</a></li></ul></nav>
        </div>
    </header>

    <div class="container auth-wrapper">
        <div class="auth-box">
            <h2>Creeaza cont nou</h2>
            <form action="action.php" method="POST">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label>Nume</label>
                    <input type="text" name="nume" required>
                </div>

                <div class="form-group">
                    <label>Prenume</label>
                    <input type="text" name="prenume" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Parola</label>
                    <input type="password" name="parola" required>
                </div>

                <button type="submit" class="btn btn-block">Inregistreaza-te</button>
            </form>
            
            <div class="auth-footer">
                Ai deja cont? <a href="login.php">Logheaza-te</a>
            </div>
        </div>
    </div>
</body>
</html>