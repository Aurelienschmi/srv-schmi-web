<?php
include_once __DIR__ . '/../../config/db.php';
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}
$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$match_id = intval($data['match_id'] ?? 0);
$map_id = $data['map_id'] ?? '';

$pdo = get_db_connection();

$match_id = intval($data['match_id'] ?? 0);
$map_id = $data['map_id'] ?? '';

$pdo = get_db_connection();

// Récupère le match
$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo json_encode(['success' => false, 'message' => 'Match introuvable']);
    exit();
}

// Vérifie que c'est bien le tour du joueur
if ($match['current_turn'] != $user_id) {
    echo json_encode(['success' => false, 'message' => "Ce n'est pas ton tour !"]);
    exit();
}

// Récupère les maps restantes
$maps_left = json_decode($match['maps_left'], true);
if (!in_array($map_id, $maps_left)) {
    echo json_encode(['success' => false, 'message' => "Map déjà bannie !"]);
    exit();
}

// Retire la map bannie
$maps_left = array_values(array_diff($maps_left, [$map_id]));

// Passe le tour à l'autre joueur
$next_turn = ($user_id == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];

// Si une seule map reste, sélectionne-la, passe le match à "ready" et tire au sort le side_picker
if (count($maps_left) == 1) {
    // Tirage au sort du joueur qui choisira le côté
    $side_picker = (rand(0, 1) === 0) ? $match['player1_id'] : $match['player2_id'];
    $stmt = $pdo->prepare("UPDATE matches SET maps_left = ?, selected_map = ?, status = 'ready', side_picker = ? WHERE id = ?");
    $stmt->execute([json_encode($maps_left), $maps_left[0], $side_picker, $match_id]);
    echo json_encode(['success' => true, 'message' => "La map sélectionnée est : " . $maps_left[0], 'finished' => true]);
    exit();
} else {
    // Sinon, update maps_left et current_turn
    $stmt = $pdo->prepare("UPDATE matches SET maps_left = ?, current_turn = ? WHERE id = ?");
    $stmt->execute([json_encode($maps_left), $next_turn, $match_id]);
    echo json_encode(['success' => true, 'message' => "Map bannie, à l'adversaire de jouer.", 'finished' => false]);
    exit();
}