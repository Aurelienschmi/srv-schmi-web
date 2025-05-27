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

// Récupère les pseudos
$getProfile = $pdo->prepare("SELECT game_name FROM game_profiles WHERE user_id = ?");
$getProfile->execute([$match['player1_id']]);
$player1_name = $getProfile->fetchColumn() ?: 'Joueur 1';
$getProfile->execute([$match['player2_id']]);
$player2_name = $getProfile->fetchColumn() ?: 'Joueur 2';

// Maps restantes
$maps_left = json_decode($match['maps_left'], true) ?: [];
$selected_map = $match['selected_map'] ?? null;
$current_turn = $match['current_turn'] ?? null;
$finished = ($match['status'] === 'ready' || count($maps_left) <= 1);

echo json_encode([
    'player1_name' => $player1_name,
    'player2_name' => $player2_name,
    'maps_left' => $maps_left,
    'selected_map' => $selected_map,
    'current_turn' => $current_turn,
    'finished' => $finished
]);
exit();