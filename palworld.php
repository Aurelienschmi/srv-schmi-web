<?php
include 'auth.php';

// Redirige si l'utilisateur n'a pas accès à cette page
if (!user_has_access('palworld.php')) {
    header('Location: acces_refuse.php');
    exit();
}

$totalTime = 0;
$playerTimes = [];

$stmt = $pdo->query("SELECT player, SUM(session_duration) as total FROM palworld_sessions WHERE session_duration IS NOT NULL GROUP BY player");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $playerTimes[$row['player']] = $row['total'];
    $totalTime += $row['total'];
}

function format_duration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf("%02dh %02dm %02ds", $h, $m, $s);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Status du serveur Palworld</title>
    <style>
        body { background:rgb(134, 134, 134); font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 420px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,0.10); padding: 36px 18px 28px 18px; text-align: center; }
        .status-on { color: #27ae60; font-size: 1.4em; }
        .status-off { color: #e74c3c; font-size: 1.4em; }
        .players-list { margin-top: 18px; }
        .player { font-size: 1.1em; margin: 4px 0; }
        .last-update { color: #888; font-size: 0.95em; margin-top: 18px; }
        .home-btn {
            display: inline-block;
            margin: 18px 0 0 0;
            background: #ff8800;
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1em;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }
        .home-btn:hover {
            background: #e67600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Status du serveur Palworld</h1>
        <?php
        $statusFile = __DIR__ . '/palworld_status.json';
        if (file_exists($statusFile)) {
            $data = json_decode(file_get_contents($statusFile), true);
            if ($data && isset($data['status'])) {
                if ($data['status'] === 'online') {
                    echo '<div class="status-on">🚀 Serveur allumé</div>';
                } else {
                    echo '<div class="status-off">🛑 Serveur éteint</div>';
                }
                if (!empty($data['players'])) {
                    echo '<div class="players-list"><strong>Joueurs connectés :</strong><br>';
                    foreach ($data['players'] as $player) {
                        echo '<div class="player">✅ ' . htmlspecialchars($player) . '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="players-list">Aucun joueur connecté.</div>';
                }
                if (!empty($data['last_update'])) {
                    echo '<div class="last-update">Dernière mise à jour : ' . htmlspecialchars($data['last_update']) . '</div>';
                }
            } else {
                echo "<div class='status-off'>Impossible de lire le statut du serveur.</div>";
            }
        } else {
            echo "<div class='status-off'>Aucune information de statut disponible.</div>";
        }
        ?>
        <div class="container" style="margin-top:30px;">
            <h2>Statistiques Palworld</h2>
            <div><strong>Temps total passé sur le serveur :</strong> <?= format_duration($totalTime) ?></div>
            <div style="margin-top:12px;">
                <strong>Temps total par joueur (Après déconnexion) :</strong>
                <ul style="list-style:none;padding:0;">
                    <?php foreach($playerTimes as $player => $time): ?>
                        <li><?= htmlspecialchars($player) ?> : <?= format_duration($time) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <a href="index.php" class="home-btn">Retour à l'accueil</a>
    </div>
</body>
</html>