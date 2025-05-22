<?php
session_start();
include_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = SHA2(?, 256)");
    $stmt->execute([$_POST['username'], $_POST['password']]);
    if ($stmt->fetch()) {
        $_SESSION['user'] = $_POST['username'];
        header('Location: index.php');
        exit();
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Palworld</title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Connexion</h2>
        <form method="post">
            <input name="username" type="text" placeholder="Nom d'utilisateur" required>
            <input name="password" type="password" placeholder="Mot de passe" required>
            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            <button>Connexion</button>
        </form>
    </div>
</body>
</html>