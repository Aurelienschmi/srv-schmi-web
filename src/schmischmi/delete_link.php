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
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM liens WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: schmischmi.php');
exit();