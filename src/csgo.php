<?php
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../config/auth.php';

if (!user_has_access('csgo.php')) {
    header("Location: acces_refuse.php");
    exit();
}


require_once __DIR__ . '/../lib/SourceQuery/Exception/SourceQueryException.php';
require_once __DIR__ . '/../lib/SourceQuery/Exception/SocketException.php';
require_once __DIR__ . '/../lib/SourceQuery/Exception/AuthenticationException.php';
require_once __DIR__ . '/../lib/SourceQuery/Exception/InvalidPacketException.php';
require_once __DIR__ . '/../lib/SourceQuery/SourceQuery.php';
require_once __DIR__ . '/../lib/SourceQuery/BaseSocket.php';
require_once __DIR__ . '/../lib/SourceQuery/Socket.php';
require_once __DIR__ . '/../lib/SourceQuery/Buffer.php';
require_once __DIR__ . '/../lib/SourceQuery/BaseRcon.php'; 
require_once __DIR__ . '/../lib/SourceQuery/SourceRcon.php';

use xPaw\SourceQuery\SourceQuery;

$Query = new SourceQuery();

define('SERVER_ADDR', '88.127.7.187');
define('SERVER_PORT', 42715);
define('RCON_PASS', 'bGMCsG8P4fLow1Jk9r7sdRgB');
define('TIMEOUT', 3);
define('ENGINE', SourceQuery::SOURCE);

$status = 'offline';
$serverName = 'Inconnu';
$players = [];
$error = '';

try {
    $Query->Connect(SERVER_ADDR, SERVER_PORT, TIMEOUT, ENGINE);
    $info = $Query->GetInfo();
    $players = $Query->GetPlayers();

    $status = 'online';
    $serverName = $info['HostName'];
} catch (Exception $e) {
    $status = 'offline';
    $error = $e->getMessage();
} finally {
    $Query->Disconnect();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Serveur CS:GO</title>
    <meta http-equiv="refresh" content="30">
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
        <h1>Statut du Serveur CS:GO</h1>
        <div class="link-card" style="flex-direction:column;align-items:center;width:100%;margin-bottom:18px;">
            <div><strong>Nom du serveur :</strong> <?= htmlspecialchars($serverName) ?></div>
            <div>
                <strong>Statut :</strong>
                <span style="font-weight:bold; color:<?= $status === 'online' ? '#27ae60' : '#e74c3c' ?>">
                    <?= $status === 'online' ? '🟢 En ligne' : '🔴 Hors ligne' ?>
                </span>
            </div>
            <?php if ($status === 'online'): ?>
                <div style="margin-top:12px;">
                    <strong>Joueurs connectés :</strong>
                    <ul style="list-style:none;padding:0;text-align:center;">
                        <?php if (count($players) > 0): ?>
                            <?php foreach ($players as $player): ?>
                                <li>
                                    <?= htmlspecialchars($player['Name']) ?>
                                    (<?= isset($player['Score']) ? $player['Score'] : '-' ?> pts)
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Aucun joueur connecté</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div style="margin-top:12px;">
                    <a class="btn" href="steam://run/730//+connect=<?= SERVER_ADDR ?>:<?= SERVER_PORT ?>">🎮 Jouer</a>
                    <a class="btn" href="steam://run/730//+connect=<?= SERVER_ADDR ?>:<?= SERVER_PORT ?>">📺 Spectate</a>
                </div>
            <?php else: ?>
                <div style="margin-top:12px;">Le serveur est actuellement hors ligne.</div>
                <?php if (!empty($error)) echo "<div style='color:#e74c3c;'><em>Erreur : ".htmlspecialchars($error)."</em></div>"; ?>
            <?php endif; ?>
        </div>
        <a href="index.php" class="btn" style="margin-top:18px;">Retour à l'accueil</a>
    </div>
</body>
</html>