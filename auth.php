<?php
session_start();
include_once 'db.php';

// Redirige vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Fonction pour vérifier l'accès à une page
function user_has_access($pagePath) {
    global $pdo;
    if (!isset($_SESSION['user'])) return false;
    // Récupérer l'id de l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username=?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
    if (!$user) return false;
    // Vérifier l'accès à la page
    $stmt = $pdo->prepare("
        SELECT 1 FROM user_pages
        JOIN pages ON user_pages.page_id = pages.id
        WHERE user_pages.user_id = ? AND pages.path = ?
    ");
    $stmt->execute([$user['id'], basename($pagePath)]);
    return $stmt->fetchColumn() !== false;
}
?>