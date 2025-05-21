<?php
include 'auth.php';
if (!user_has_access('csgo.php')) {
    header("Location: acces_refuse.php");
    exit();
}

// Inclusion manuelle des fichiers SourceQuery
require_once __DIR__ . '/lib/SourceQuery/Exception/SourceQueryException.php';
require_once __DIR__ . '/lib/SourceQuery/Exception/SocketException.php';
require_once __DIR__ . '/lib/SourceQuery/Exception/AuthenticationException.php';
require_once __DIR__ . '/lib/SourceQuery/Exception/InvalidPacketException.php';
require_once __DIR__ . '/lib/SourceQuery/SourceQuery.php';
require_once __DIR__ . '/lib/SourceQuery/BaseSocket.php';
require_once __DIR__ . '/lib/SourceQuery/Socket.php';
require_once __DIR__ . '/lib/SourceQuery/Buffer.php';
require_once __DIR__ . '/lib/SourceQuery/BaseRcon.php'; // Ajouté pour corriger l'erreur
require_once __DIR__ . '/lib/SourceQuery/SourceRcon.php';
require_once __DIR__ . '/lib/SourceQuery/Exception/SocketException.php';
require_once __DIR__ . '/lib/SourceQuery/Exception/AuthenticationException.php';
require_once __DIR__ . '/lib/SourceQuery/Exception/InvalidPacketException.php';

use xPaw\SourceQuery\SourceQuery;

$Query = new SourceQuery();

define('SERVER_ADDR', '192.168.1.185');
define('SERVER_PORT', 27015);
define('RCON_PASS', 'votre_mdp_rcon');
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
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #1e1e1e;
            color: white;
            text-align: center;
            padding: 30px;
        }
        .server {
            background-color: #2e2e2e;
            border-radius: 12px;
            padding: 20px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.6);
        }
        .status {
            font-weight: bold;
            color: <?php echo ($status === 'online') ? '#4CAF50' : '#f44336'; ?>;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        .button {
            background-color: #2196F3;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .button:hover {
            background-color: #1976D2;
        }
        .home-btn {
            display: inline-block;
            margin-top: 18px;
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
    <div class="server">
        <h1>Statut du Serveur CS:GO</h1>
        <p>Nom du serveur : <strong><?= htmlspecialchars($serverName) ?></strong></p>
        <p>Statut : <span class="status"><?= $status ?></span></p>

        <?php if ($status === 'online'): ?>
            <h3>Joueurs connectés :</h3>
            <ul>
                <?php if (count($players) > 0): ?>
                    <?php foreach ($players as $player): ?>
                        <li><?= htmlspecialchars($player['Name']) ?> (<?= $player['Score'] ?> pts)</li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Aucun joueur connecté</li>
                <?php endif; ?>
            </ul>
            <a class="button" href="steam://connect/<?= SERVER_ADDR ?>:<?= SERVER_PORT ?>">🎮 Jouer</a>
            <a class="button" href="steam://connect/<?= SERVER_ADDR ?>:<?= SERVER_PORT ?>">📺 Spectate</a>
        <?php else: ?>
            <p>Le serveur est actuellement hors ligne.</p>
            <?php if (!empty($error)) echo "<p><em>Erreur : ".htmlspecialchars($error)."</em></p>"; ?>
        <?php endif; ?>
        <a href="index.php" class="home-btn">Retour à l'accueil</a>
    </div>
</body>
</html>