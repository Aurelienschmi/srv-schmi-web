<?php
include_once __DIR__ . '/../../config/db.php';
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non authentifié']);
    exit();
}
$user_id = $_SESSION['user_id'];

// Récupère l'action envoyée en JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

$pdo = get_db_connection();

if ($action === 'leave') {
    // Retire de la file d'attente
    $stmt = $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?");
    $stmt->execute([$user_id]);
    // Termine le match en attente si existant
    $stmt = $pdo->prepare("UPDATE matches SET status = 'finished' WHERE (player1_id = ? OR player2_id = ?) AND status = 'waiting'");
    $stmt->execute([$user_id, $user_id]);
    echo json_encode(['status' => 'left']);
    exit();
}

if ($action === 'join') {
    // Vérifie si déjà en file
    $stmt = $pdo->prepare("SELECT id FROM matchmaking_queue WHERE user_id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) {
        // Vérifie si un match existe déjà pour ce joueur
        $stmt = $pdo->prepare("SELECT id FROM matches WHERE (player1_id = ? OR player2_id = ?) AND status = 'waiting'");
        $stmt->execute([$user_id, $user_id]);
        if ($match = $stmt->fetch()) {
            echo json_encode(['status' => 'ready', 'match_id' => $match['id']]);
            exit();
        }
        echo json_encode(['status' => 'already_in_queue']);
        exit();
    }

    // Cherche un autre joueur en file (différent de soi)
    $stmt = $pdo->prepare("SELECT user_id FROM matchmaking_queue WHERE user_id != ? ORDER BY joined_at ASC LIMIT 1");
    $stmt->execute([$user_id]);
    $opponent = $stmt->fetchColumn();

    if ($opponent) {
        // Initialisation des maps et du tour
        $all_maps = json_encode([
            'aim_map',
            'aim_map_cartoon',
            'aim_centro',
            'aim_chess',
            'awp_lego'
        ]);
        // Le joueur qui attendait le plus longtemps commence (ici $opponent)
        $stmt = $pdo->prepare("INSERT INTO matches (player1_id, player2_id, maps_left, current_turn) VALUES (?, ?, ?, ?)");
        $stmt->execute([$opponent, $user_id, $all_maps, $opponent]);
        $match_id = $pdo->lastInsertId();

        // Retire les deux joueurs de la file
        $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id IN (?, ?)")->execute([$user_id, $opponent]);

        echo json_encode(['status' => 'ready', 'match_id' => $match_id]);
        exit();
    } else {
        // Ajoute à la file
        $stmt = $pdo->prepare("INSERT INTO matchmaking_queue (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        echo json_encode(['status' => 'waiting']);
        exit();
    }
}

if ($action === 'status') {
    // Vérifie si un match existe déjà pour ce joueur
    $stmt = $pdo->prepare("SELECT id FROM matches WHERE (player1_id = ? OR player2_id = ?) AND status = 'waiting'");
    $stmt->execute([$user_id, $user_id]);
    if ($match = $stmt->fetch()) {
        echo json_encode(['status' => 'ready', 'match_id' => $match['id']]);
        exit();
    }
    echo json_encode(['status' => 'waiting']);
    exit();
}

// Par défaut
echo json_encode(['status' => 'error', 'message' => 'Action inconnue']);
exit();