<?php
include 'auth.php';
include 'db.php';

if (!user_has_access('admin.php')) {
    header('Location: acces_refuse.php');
    exit();
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM liens WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: schmischmi.php');
exit();

