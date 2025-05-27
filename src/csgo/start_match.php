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
    </div>
</body>
</html>