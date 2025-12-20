<?php
function set_active($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page_name) ? 'active' : '';
}
?>

<link rel="stylesheet" href="./resources/css/style.css">

<header>
    <div class="container">
        <div id="branding">
            <h1><a href="index.php" style="all:unset">Biblioteca</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class=<?= set_active('index.php') ?>>Acasa</a></li>
                <li><a href="about.php" class=<?= set_active('about.php') ?>>Despre</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php" class=<?= set_active('profile.php') ?>>Profilul Meu</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php" class=<?= set_active('admin.php') ?>>Administrare</a></li>
                    <?php endif; ?>
                    <li><a href="./services/action.php?action=logout&csrf_token=<?= $_SESSION['csrf_token'] ?>">Logout (<?= $_SESSION['email'] ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Autentificare</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>