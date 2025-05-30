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
if (!is_array($maps_left) || !in_array($map_id, $maps_left)) {
    echo json_encode(['success' => false, 'message' => "Map déjà bannie ou invalide !"]);
    exit();
}

// Retire la map bannie
$maps_left = array_values(array_diff($maps_left, [$map_id]));

// Passe le tour à l'autre joueur
$next_turn = ($user_id == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];

// Tableau de correspondance map => workshop ID
$maps_workshop = [
    'aim_map'         => '3084291314',
    'aim_map_cartoon' => '3218219558',
    'aim_centro'      => '3243596725',
    'aim_chess'       => '3324226065',
    'awp_lego'        => '3146105097',
];

// Si une seule map reste, sélectionne-la, passe le match à "ready" et tire au sort le side_picker
if (count($maps_left) == 1) {
    $selected_map_key = $maps_left[0];
    $side_picker = (rand(0, 1) === 0) ? $match['player1_id'] : $match['player2_id'];
    $stmt = $pdo->prepare("UPDATE matches SET maps_left = ?, selected_map = ?, status = 'ready', side_picker = ? WHERE id = ?");
    $stmt->execute([json_encode($maps_left), $selected_map_key, $side_picker, $match_id]);

    // Lancer le serveur avec l'ID workshop correspondant
    if (isset($maps_workshop[$selected_map_key])) {
        $workshop_id = escapeshellarg($maps_workshop[$selected_map_key]);
        exec("sudo systemctl start csgo-server@$workshop_id 2>&1", $output, $result);
    }

    echo json_encode(['success' => true, 'message' => "La map sélectionnée est : " . $selected_map_key, 'finished' => true]);
    exit();
} else {
    // Sinon, update maps_left et current_turn
    $stmt = $pdo->prepare("UPDATE matches SET maps_left = ?, current_turn = ? WHERE id = ?");
    $stmt->execute([json_encode($maps_left), $next_turn, $match_id]);
    echo json_encode(['success' => true, 'message' => "Map bannie, à l'adversaire de jouer.", 'finished' => false]);
    exit();
}