<?php
include_once __DIR__ . '/../../config/db.php';
session_start();
header('Content-Type: application/json');

$pdo = get_db_connection();
$waiting = $pdo->query("SELECT user_id FROM matchmaking_queue ORDER BY joined_at ASC")->fetchAll(PDO::FETCH_COLUMN);

$waiting_profiles = [];
if ($waiting) {
    $in  = implode(',', array_map('intval', $waiting));
    $stmt = $pdo->query("SELECT user_id, (SELECT game_name FROM game_profiles WHERE user_id = q.user_id) as game_name FROM matchmaking_queue q WHERE user_id IN ($in)");
    $waiting_profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($waiting_profiles);
exit();