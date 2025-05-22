<?php
// Démarre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function is_logged_in() {
    return !empty($_SESSION['user']);
}

/**
 * Vérifie si l'utilisateur a accès à une page donnée
 * @param string $page
 * @return bool
 */
function user_has_access($page) {
    global $pdo;
    if (!is_logged_in()) return false;

    // Récupère le rôle de l'utilisateur
    $stmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $role = $stmt->fetchColumn();

    // Si admin, accès à tout
    if ($role === 'admin') return true;

    // Sinon, logique classique (exemple : accès à admin.php refusé)
    if ($page === 'admin.php') return false;

    // Sinon, accès autorisé (ou adapte selon ta logique)
    return true;
}