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

// Récupère le match et les infos nécessaires
$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo "Match introuvable.";
    exit();
}

// Récupère les pseudos
$getProfile = $pdo->prepare("SELECT game_name FROM game_profiles WHERE user_id = ?");
$getProfile->execute([$match['player1_id']]);
$player1_name = $getProfile->fetchColumn() ?: 'Joueur 1';
$getProfile->execute([$match['player2_id']]);
$player2_name = $getProfile->fetchColumn() ?: 'Joueur 2';

$side_picker = $match['side_picker'];
$selected_side = $match['selected_side'] ?? null; // Ajoute cette colonne dans ta table si besoin

// Traitement du choix du côté
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id == $side_picker && !$selected_side) {
    $side = $_POST['side'] ?? '';
    if ($side === 'ct' || $side === 't') {
        $stmt = $pdo->prepare("UPDATE matches SET selected_side = ? WHERE id = ?");
        $stmt->execute([$side, $match_id]);
        header("Location: choose_side.php?match_id=" . $match_id);
        exit();
    }
}

// Recharge le match pour avoir la valeur à jour
$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);
$selected_side = $match['selected_side'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix du côté</title>
    <link rel="stylesheet" href="/public/styles.css">
    <style>
        .side-btn {
            padding: 12px 32px;
            margin: 12px;
            font-size: 1.2em;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .ct-btn { background: #3498db; color: #fff; }
        .t-btn { background: #e67e22; color: #fff; }
        .side-btn:disabled { background: #888; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:500px;">
        <h1>Choix du côté</h1>
        <div style="margin-bottom:18px;">
            <b><?= htmlspecialchars($player1_name) ?></b> vs <b><?= htmlspecialchars($player2_name) ?></b><br>
            <span style="color:#888;">Map : <?= htmlspecialchars($match['selected_map'] ?? 'Inconnue') ?></span>
        </div>
        <?php if ($selected_side): ?>
            <div style="font-size:1.2em;margin-bottom:18px;">
                <b>
                <?php
                    $side_name = $selected_side === 'ct' ? 'CT (Counter-Terrorist)' : 'T (Terrorist)';
                    if ($side_picker == $match['player1_id']) {
                        $picker_name = $player1_name;
                    } else {
                        $picker_name = $player2_name;
                    }
                    echo htmlspecialchars($picker_name) . " a choisi le côté : " . $side_name;
                ?>
                </b>
            </div>
            <a href="start_match.php?match_id=<?= $match_id ?>" class="btn">Continuer</a>
        <?php elseif ($user_id == $side_picker): ?>
            <div style="margin-bottom:12px;color:#27ae60;">
                C'est à toi de choisir ton côté !
            </div>
            <form method="post">
                <button type="submit" name="side" value="ct" class="side-btn ct-btn">CT</button>
                <button type="submit" name="side" value="t" class="side-btn t-btn">T</button>
            </form>
        <?php else: ?>
            <div style="margin-bottom:12px;color:#e67e22;">
                En attente que l'adversaire choisisse son côté...
            </div>
            <script>
                setInterval(function() {
                    fetch('choose_side.php?match_id=<?= $match_id ?>&ajax=1')
                        .then(r => r.text())
                        .then(html => {
                            if (html.includes('a choisi le côté')) {
                                location.reload();
                            }
                        });
                }, 2000);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>