<?php
include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../config/auth.php';

if (!user_has_access('csgo.php')) {
    header("Location: ../acces_refuse.php");
    exit();
}

if (empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifie si le profil de jeu CS:GO existe
$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT id FROM game_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
    // Redirige vers la page de création de profil de jeu
    header("Location: csgo_create_profile.php");
    exit();
}


require_once __DIR__ . '/../../lib/SourceQuery/Exception/SourceQueryException.php';
require_once __DIR__ . '/../../lib/SourceQuery/Exception/SocketException.php';
require_once __DIR__ . '/../../lib/SourceQuery/Exception/AuthenticationException.php';
require_once __DIR__ . '/../../lib/SourceQuery/Exception/InvalidPacketException.php';
require_once __DIR__ . '/../../lib/SourceQuery/SourceQuery.php';
require_once __DIR__ . '/../../lib/SourceQuery/BaseSocket.php';
require_once __DIR__ . '/../../lib/SourceQuery/Socket.php';
require_once __DIR__ . '/../../lib/SourceQuery/Buffer.php';
require_once __DIR__ . '/../../lib/SourceQuery/BaseRcon.php'; 
require_once __DIR__ . '/../../lib/SourceQuery/SourceRcon.php';

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

// Récupère les matchs en cours
$stmt = $pdo->query("
    SELECT m.id, m.player1_id, m.player2_id, m.selected_map, 
           gp1.game_name AS player1_name, gp2.game_name AS player2_name
    FROM matches m
    LEFT JOIN game_profiles gp1 ON m.player1_id = gp1.user_id
    LEFT JOIN game_profiles gp2 ON m.player2_id = gp2.user_id
    WHERE m.status IN ('ready', 'banning')
    ORDER BY m.id DESC
");
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT game_name, profile_image FROM game_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$game_profile = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_image_url = !empty($game_profile['profile_image'])
    ? "/public/profiles/" . $game_profile['profile_image']
    : "/public/profiles/default_profile.png";
$game_name = !empty($game_profile['game_name']) ? htmlspecialchars($game_profile['game_name']) : htmlspecialchars($_SESSION['user']);
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
        <div style="text-align:center;">
            <a href="account.php" style="text-decoration:none; color:inherit;">
                <img src="<?= $profile_image_url ?>" alt="Profil" class="profile-img-preview">
                <div style="font-size:14px;margin-top:4px;"><?= $game_name ?></div>
            </a>
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
        <a href="/src/index.php" class="btn" style="margin-top:18px;">Retour à l'accueil</a>
        <a href="queu.php" class="btn" style="margin-top:10px;">🎮 Jouer 1vs1</a>

    </div>
    <?php if (!empty($matches)): ?>
    <h2>Matchs en cours</h2>
    <table class="table" style="width:100%;margin-bottom:24px;">
        <thead>
            <tr>
                <th>Joueur 1</th>
                <th>Joueur 2</th>
                <th>Map</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($matches as $match): ?>
            <tr>
                <td><?= htmlspecialchars($match['player1_name'] ?? 'Joueur 1') ?></td>
                <td><?= htmlspecialchars($match['player2_name'] ?? 'Joueur 2') ?></td>
                <td><?= htmlspecialchars($match['selected_map'] ?? 'En sélection') ?></td>
                <td>
                    <a class="btn" href="steam://run/730//+connect=88.127.7.187:42715" target="_blank">Regarder</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Aucun match en cours.</p>
<?php endif; ?>
</body>
</html>