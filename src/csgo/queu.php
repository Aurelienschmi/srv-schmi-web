<?php
include_once __DIR__ . '/../../config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Vérifie si le profil de jeu CS:GO existe
$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT id FROM game_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
    header("Location: csgo_create_profile.php");
    exit();
}

// Vérifie si déjà en file
$stmt = $pdo->prepare("SELECT id FROM matchmaking_queue WHERE user_id = ?");
$stmt->execute([$user_id]);
$already_in_queue = $stmt->fetch() ? true : false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Jouer 1vs1</title>
    <link rel="stylesheet" href="/public/styles.css">
    <script>
        let polling = <?= $already_in_queue ? 'true' : 'false' ?>;
        let pollInterval = null;

        function joinQueue() {
            fetch('/src/api/matchmaking.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'join'})
            })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'waiting' || data.status === 'already_in_queue') {
                    document.getElementById('queue-status').innerText = "En attente d'un adversaire...";
                    document.getElementById('queue-btn').innerText = "Annuler";
                    document.getElementById('queue-btn').onclick = leaveQueue;
                    if (!polling) {
                        polling = true;
                        pollInterval = setInterval(checkStatus, 2000);
                    }
                } else if(data.status === 'ready') {
                    window.location.href = 'waiting_room.php?match_id=' + data.match_id;
                } else if(data.message) {
                    document.getElementById('queue-status').innerText = data.message;
                }
                refreshWaitingList();
            })
            .catch(() => {
                document.getElementById('queue-status').innerText = "Erreur de connexion au serveur.";
            });
        }

        function leaveQueue() {
            fetch('/src/api/matchmaking.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'leave'})
            })
            .then(r => r.json())
            .then(() => {
                document.getElementById('queue-status').innerText = "Tu as quitté la file d'attente.";
                document.getElementById('queue-btn').innerText = "Entrer dans la file";
                document.getElementById('queue-btn').onclick = joinQueue;
                polling = false;
                if (pollInterval) clearInterval(pollInterval);
                refreshWaitingList();
            });
        }

        function checkStatus() {
            fetch('/src/api/matchmaking.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'status'})
            })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'ready') {
                    clearInterval(pollInterval);
                    window.location.href = 'waiting_room.php?match_id=' + data.match_id;
                }
            });
        }

        function refreshWaitingList() {
            fetch('/src/api/waiting_list.php')
                .then(r => r.json())
                .then(list => {
                    let html = '';
                    if (list.length === 0) {
                        html = '<li>Aucun joueur en attente</li>';
                    } else {
                        list.forEach(w => {
                            html += `<li>${w.game_name ? w.game_name : 'Joueur ' + w.user_id}${w.user_id == <?= $user_id ?> ? ' (toi)' : ''}</li>`;
                        });
                    }
                    document.getElementById('waiting-list').innerHTML = html;
                });
        }

        window.onload = function() {
            if (polling) {
                pollInterval = setInterval(checkStatus, 2000);
            }
            refreshWaitingList();
            setInterval(refreshWaitingList, 2000);
        };
    </script>
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Matchmaking 1vs1</h1>
        <div id="queue-status"><?= $already_in_queue ? "En attente d'un adversaire..." : "Clique sur le bouton pour entrer dans la file d’attente." ?></div>
        <button class="btn" id="queue-btn" onclick="<?= $already_in_queue ? 'leaveQueue()' : 'joinQueue()' ?>">
            <?= $already_in_queue ? 'Annuler' : 'Entrer dans la file' ?>
        </button>
        <div style="margin-top:24px;">
            <strong>Joueurs en attente :</strong>
            <ul id="waiting-list" style="padding-left:18px;">
                <!-- Rempli dynamiquement par JS -->
            </ul>
        </div>
    </div>
</body>
</html>