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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion à la partie</title>
    <link rel="stylesheet" href="/public/styles.css">
    <style>
        .player-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        .player-row img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid #888;
            background: #232323;
        }
        #wait-timer {
            margin-bottom: 10px;
            color: #888;
        }
        @media (max-width: 600px) {
            .admin-container {
                max-width: 98vw !important;
            }
            .player-row img {
                width: 36px;
                height: 36px;
            }
        }
    </style>
    <script>
        let countdownStarted = false;
        let seconds = 0;
        let pollInterval = null;

        function pollMatch() {
            fetch('/src/api/match_status.php?match_id=<?= $match_id ?>')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('player1').innerText = data.player1_name + (data.player1_id == <?= $user_id ?> ? " (toi)" : "");
                    document.getElementById('player2').innerText = data.player2_name + (data.player2_id == <?= $user_id ?> ? " (toi)" : "");
                    document.getElementById('img1').src = data.player1_img;
                    document.getElementById('img2').src = data.player2_img;
                    document.getElementById('status1').innerHTML = data.player1_connected ? "<span style='color:#27ae60'>🟢 Connecté</span>" : "<span style='color:#e67e22'>🕓 Connexion...</span>";
                    document.getElementById('status2').innerHTML = data.player2_connected ? "<span style='color:#27ae60'>🟢 Connecté</span>" : "<span style='color:#e67e22'>🕓 Connexion...</span>";
                    if (data.player1_connected && data.player2_connected && !countdownStarted) {
                        countdownStarted = true;
                        startCountdown();
                    }
                })
                .catch(() => {
                    document.getElementById('wait-timer').innerText = "Erreur de connexion au serveur.";
                });
        }

        function startCountdown() {
            let count = 5;
            const el = document.getElementById('countdown');
            el.style.display = 'block';
            el.innerText = "Début dans " + count + " secondes...";
            const interval = setInterval(() => {
                count--;
                el.innerText = "Début dans " + count + " secondes...";
                if (count <= 0) {
                    clearInterval(interval);
                    // Vérifie s'il y a un match en cours avant de démarrer
                    fetch('/src/api/csgo_match_status.php')
                        .then(r => r.json())
                        .then(data => {
                            if (data.running) {
                                el.innerText = "Un match est déjà en cours. Merci de patienter...";
                                setTimeout(startCountdown, 3000); // Réessaie dans 3 secondes
                            } else {
                                window.location.href = "ban_maps.php?match_id=<?= $match_id ?>";
                            }
                        });
                }
            }, 1000);
        }

        function quitWaitingRoom() {
            fetch('/src/api/matchmaking.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'leave'})
            }).then(() => {
                window.location.href = "queu.php";
            });
        }

        window.onload = function() {
            pollMatch();
            pollInterval = setInterval(pollMatch, 1000);
            setInterval(() => {
                seconds++;
                document.getElementById('wait-timer').innerText = "Attente : " + seconds + "s";
            }, 1000);
        };
        window.onbeforeunload = function() {
            return "Es-tu sûr de vouloir quitter la salle d'attente ?";
        };
    </script>
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Match trouvé !</h1>
        <div id="wait-timer"></div>
        <div class="player-row">
            <img id="img1" src="/public/profiles/default_profile.png" alt="Ton avatar">
            <span id="player1"></span>
            <span id="status1" style="margin-left:8px;"></span>
        </div>
        <div class="player-row">
            <img id="img2" src="/public/profiles/default_profile.png" alt="Avatar adversaire">
            <span id="player2"></span>
            <span id="status2" style="margin-left:8px;"></span>
        </div>
        <div style="margin-top:18px;color:#aaa;">
            Quand les deux joueurs sont connectés, la sélection de map commence !
        </div>
        <div id="countdown" style="display:none;font-size:22px;margin-top:20px;"></div>
        <button onclick="quitWaitingRoom()" style="margin-top:24px;" class="btn">Quitter la salle d'attente</button>
    </div>
</body>
</html>