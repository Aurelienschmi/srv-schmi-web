<?php
include_once __DIR__ . '/../config/auth.php';
include_once __DIR__ . '/../config/db.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:520px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <a href="index.php" class="btn">Accueil</a>
            </div>
            <div>
                <?php if (!empty($_SESSION['user'])): ?>
                    <span class="user-info"><?= htmlspecialchars($_SESSION['user']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <h1>Status du serveur Palworld</h1>
        <?php
$statusFile = __DIR__ . '/../cache/palworld_status.json';
        if (file_exists($statusFile)) {
            $data = json_decode(file_get_contents($statusFile), true);
            if ($data && isset($data['status'])) {
                if ($data['status'] === 'online') {
                    echo '<div class="status-on" style="color:#27ae60;font-size:1.4em;">🚀 Serveur allumé</div>';
                } else {
                    echo '<div class="status-off" style="color:#e74c3c;font-size:1.4em;">🛑 Serveur éteint</div>';
                }
                if (!empty($data['players'])) {
                    echo '<div class="players-list" style="margin-top:18px;"><strong>Joueurs connectés :</strong><br>';
                    foreach ($data['players'] as $player) {
                        echo '<div class="player" style="font-size:1.1em;margin:4px 0;">✅ ' . htmlspecialchars($player) . '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="players-list" style="margin-top:18px;">Aucun joueur connecté.</div>';
                }
                if (!empty($data['last_update'])) {
                    echo '<div class="last-update" style="color:#888;font-size:0.95em;margin-top:18px;">Dernière mise à jour : ' . htmlspecialchars($data['last_update']) . '</div>';
                }
            } else {
                echo "<div class='status-off' style='color:#e74c3c;font-size:1.2em;'>Impossible de lire le statut du serveur.</div>";
            }
        } else {
            echo "<div class='status-off' style='color:#e74c3c;font-size:1.2em;'>Aucune information de statut disponible.</div>";
        }
        ?>
        <div class="link-card stats-palworld">
            <h2>Statistiques Palworld</h2>
            <div><strong>Temps total passé sur le serveur :</strong> <?= format_duration($totalTime) ?></div>
            <div style="margin-top:12px;">
                <strong>Temps total par joueur (Après déconnexion) :</strong>
                <ul>
                    <?php foreach($playerTimes as $player => $time): ?>
                        <li><?= htmlspecialchars($player) ?> : <?= format_duration($time) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <a href="index.php" class="btn" style="margin-top:18px;">Retour à l'accueil</a>
    </div>
</body>
</html>