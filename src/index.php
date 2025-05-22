<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="schmischmi-container" style="max-width:520px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div></div>
            <div>
                <?php if (!empty($_SESSION['user'])): ?>
                    <span class="user-info"><?= htmlspecialchars($_SESSION['user']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <h1>Accueil</h1>
        <div class="schmischmi-links">
            <div class="link-card">
                <a class="main-link" href="login.php">Login</a>
            </div>
            <div class="link-card">
                <a class="main-link" href="csgo.php">CSGO</a>
            </div>
            <div class="link-card">
                <a class="main-link" href="palworld.php">Palworld</a>
            </div>
            <div class="link-card">
                <a class="main-link" href="schmischmi/schmischmi.php">Schmischmi</a>
            </div>
            <div class="link-card">
                <a class="main-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>