<?php
include_once __DIR__ . '/../../config/auth.php';
include_once __DIR__ . '/../../config/db.php';

// Vérifie si l'utilisateur a le rôle admin dans la base
$isAdmin = false;
if (!empty($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $role = $stmt->fetchColumn();
    if ($role === 'admin') {
        $isAdmin = true;
    }
}

if (!$isAdmin) {
    header('Location: ../acces_refuse.php');
    exit();
}

$message = '';
if (!empty($_POST['label']) && !empty($_POST['url'])) {
    $stmt = $pdo->prepare('INSERT INTO liens (label, url) VALUES (?, ?)');
    $stmt->execute([$_POST['label'], $_POST['url']]);
    header('Location: schmischmi.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un lien</title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Ajouter un lien</h1>
        <form method="post" class="add-link-form">
            <label for="label">Label</label>
            <input type="text" id="label" name="label" required>
            <label for="url">URL</label>
            <input type="url" id="url" name="url" required>
            <button type="submit" class="btn" style="width:100%;margin-top:18px;">Ajouter</button>
            <a href="schmischmi.php" class="btn" style="background:#aaa;width:100%;margin-top:10px;">Annuler</a>
        </form>
    </div>
</body>
</html>