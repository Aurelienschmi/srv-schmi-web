<?php
session_start();
include 'db.php';

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
    <style>
        body {
            background:rgb(85, 83, 83);
            font-family: Arial, sans-serif;
            color: #333;
        }
        .login-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 350px;
            margin: 60px auto;
            padding: 32px 28px 24px 28px; /* même valeur à droite et à gauche */
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            box-sizing: border-box; /* Ajouté pour corriger le calcul du padding */
        }
        h2 {
            color: #ff8800;
            text-align: center;
            margin-bottom: 24px;
        }
        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #f7f7f7;
            font-size: 16px;
        }
        button {
            width: 100%;
            background: #ff8800;
            color: #fff;
            border: none;
            padding: 12px 0;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #e67600;
        }
        .error {
            color: #fff;
            background:rgb(255, 94, 0);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            text-align: center;
        }
    </style>
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