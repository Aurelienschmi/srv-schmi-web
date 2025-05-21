<?php
include 'auth.php';
include 'db.php';

if (!user_has_access('admin.php')) {
    header('Location: acces_refuse.php');
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
<h2>Ajouter un lien</h2>
<form method="post">
    <label>Label : <input type="text" name="label" required></label><br>
    <label>URL : <input type="url" name="url" required></label><br>
    <button type="submit">Ajouter</button>
    <a href="schmischmi.php">Annuler</a>
</form>
