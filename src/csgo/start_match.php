<?php
include_once __DIR__ . '/../../config/db.php';
session_start();
if (empty($_SESSION['user_id']) || empty($_GET['match_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$match_id = intval($_GET['match_id']);

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo "Match introuvable.";
    exit();
}

// Mets ici l'IP et le port de ton serveur CS:GO
$ip = "88.127.7.187";
$port = "42715";

// Optionnel : affiche la map et le side choisi
$map = $match['selected_map'] ?? 'Inconnue';
$side = $match['selected_side'] ?? null;
$side_label = $side === 'ct' ? 'CT (Counter-Terrorist)' : ($side === 't' ? 'T (Terrorist)' : 'Non défini');

// Tableau de correspondance map => workshop ID
$maps_workshop = [
    'aim_map'         => '3084291314',
    'aim_map_cartoon' => '3218219558',
    'aim_centro'      => '3243596725',
    'aim_chess'       => '3324226065',
    'awp_lego'        => '3146105097',
];

// Si le bouton "Arrêter le match" est cliqué
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stop_match'])) {
    // Met à jour le statut du match
    $stmt = $pdo->prepare("UPDATE matches SET status = 'finished' WHERE id = ?");
    $stmt->execute([$match_id]);

    // Arrête le service systemd correspondant
    $selected_map_key = $match['selected_map'] ?? null;
    if ($selected_map_key && isset($maps_workshop[$selected_map_key])) {
        $workshop_id = escapeshellarg($maps_workshop[$selected_map_key]);
        exec("sudo systemctl stop csgo-server@$workshop_id 2>&1");
    } else {
        exec("sudo systemctl stop 'csgo-server@*' 2>&1");
    }

    // Recharge le match pour afficher le bon statut
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->execute([$match_id]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Si le match est déjà terminé, arrête le service (sécurité)
if ($match['status'] === 'finished') {
    $selected_map_key = $match['selected_map'] ?? null;
    if ($selected_map_key && isset($maps_workshop[$selected_map_key])) {
        $workshop_id = escapeshellarg($maps_workshop[$selected_map_key]);
        exec("sudo systemctl stop csgo-server@$workshop_id 2>&1");
    } else {
        exec("sudo systemctl stop 'csgo-server@*' 2>&1");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prêt à jouer</title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Prêt à jouer !</h1>
        <p>Map sélectionnée : <b><?= htmlspecialchars($map) ?></b></p>
        <p>Côté choisi : <b><?= htmlspecialchars($side_label) ?></b></p>
        <p>Quand tu es prêt, clique sur le bouton ci-dessous pour lancer CS:GO et rejoindre le serveur.</p>
        <a href="steam://run/730//+connect=<?= $ip ?>:<?= $port ?>" class="btn" style="font-size:1.3em;">JOUER</a>
        <form method="post" style="margin-top:24px;">
            <button type="submit" name="stop_match" class="btn" style="background:#e74c3c;">Arrêter le match</button>
        </form>
        <?php if ($match['status'] === 'finished'): ?>
            <div style="margin-top:18px;color:#27ae60;font-weight:bold;">Le match est terminé.</div>
        <?php endif; ?>
    </div>
</body>
</html>