<?php
include_once __DIR__ . '/../../config/db.php';
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id']) || empty($_GET['match_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}
$user_id = $_SESSION['user_id'];
$match_id = intval($_GET['match_id']);
$pdo = get_db_connection();

// Récupère le match
$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo json_encode(['error' => 'Match introuvable']);
    exit();
}

// Marque ce joueur comme connecté
if ($user_id == $match['player1_id'] && !$match['player1_connected']) {
    $pdo->prepare("UPDATE matches SET player1_connected = 1 WHERE id = ?")->execute([$match_id]);
    $match['player1_connected'] = 1;
}
if ($user_id == $match['player2_id'] && !$match['player2_connected']) {
    $pdo->prepare("UPDATE matches SET player2_connected = 1 WHERE id = ?")->execute([$match_id]);
    $match['player2_connected'] = 1;
}

// Récupère les pseudos (ou ce que tu veux afficher)
$getName = $pdo->prepare("SELECT game_name FROM game_profiles WHERE user_id = ?");
$getName->execute([$match['player1_id']]);
$player1_name = $getName->fetchColumn() ?: 'Joueur 1';

$getName->execute([$match['player2_id']]);
$player2_name = $getName->fetchColumn() ?: 'Joueur 2';

// Ajoute après la récupération des pseudos
$getImg = $pdo->prepare("SELECT profile_image FROM game_profiles WHERE user_id = ?");
$getImg->execute([$match['player1_id']]);
$player1_img = $getImg->fetchColumn() ?: 'default_profile.png';

$getImg->execute([$match['player2_id']]);
$player2_img = $getImg->fetchColumn() ?: 'default_profile.png';

// Dans le json final
echo json_encode([
    'player1_name' => $player1_name,
    'player2_name' => $player2_name,
    'player1_img' => '/public/profiles/' . $player1_img,
    'player2_img' => '/public/profiles/' . $player2_img,
    'player1_connected' => (bool)$match['player1_connected'],
    'player2_connected' => (bool)$match['player2_connected']
]);
exit();