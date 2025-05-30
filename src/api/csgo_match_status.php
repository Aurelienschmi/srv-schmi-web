<?php
include_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$pdo = get_db_connection();

// Vérifie s'il existe un match en cours (status différent de 'finished')
$stmt = $pdo->query("SELECT COUNT(*) FROM matches WHERE status IN ('ready', 'banning')");
$running = $stmt->fetchColumn() > 0;

echo json_encode(['running' => $running]);