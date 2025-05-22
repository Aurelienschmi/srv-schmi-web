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

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: schmischmi.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM liens WHERE id = ?');
$stmt->execute([$id]);
$lien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lien) {
    header('Location: schmischmi.php');
    exit();
}

if (!empty($_POST['label']) && !empty($_POST['url'])) {
    $stmt = $pdo->prepare('UPDATE liens SET label = ?, url = ? WHERE id = ?');
    $stmt->execute([$_POST['label'], $_POST['url'], $id]);
    header('Location: schmischmi.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le lien</title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Modifier le lien</h1>
        <form method="post" class="add-link-form">
            <label for="label">Label</label>
            <input type="text" id="label" name="label" value="<?= htmlspecialchars($lien['label']) ?>" required>
            <label for="url">URL</label>
            <input type="url" id="url" name="url" value="<?= htmlspecialchars($lien['url']) ?>" required>
            <button type="submit" class="btn full-width-btn" style="margin-top:18px;">Enregistrer</button>
            <a href="schmischmi.php" class="btn full-width-btn btn-grey">Annuler</a>
        </form>
    </div>
</body>
</html>