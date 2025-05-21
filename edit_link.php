<?php
include 'auth.php';
include 'db.php';

if (!user_has_access('admin.php')) {
    header('Location: acces_refuse.php');
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
<h2>Modifier le lien</h2>
<form method="post">
    <label>Label : <input type="text" name="label" value="<?= htmlspecialchars($lien['label']) ?>" required></label><br>
    <label>URL : <input type="url" name="url" value="<?= htmlspecialchars($lien['url']) ?>" required></label><br>
    <button type="submit">Enregistrer</button>
    <a href="schmischmi.php">Annuler</a>
</form>
