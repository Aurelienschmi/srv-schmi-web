<?php
include_once __DIR__ . '/../../config/db.php';
session_start();
if (empty($_SESSION['user_id']) || empty($_GET['match_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$match_id = intval($_GET['match_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ban des maps</title>
    <link rel="stylesheet" href="/public/styles.css">
    <style>
        .map-list {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 24px;
        }
        .map-card {
            background: #232323;
            border-radius: 8px;
            padding: 12px;
            width: 180px;
            text-align: center;
            box-shadow: 0 2px 8px #0002;
        }
        .map-card img {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 8px;
            background: #111;
        }
        .ban-btn {
            margin-top: 8px;
            padding: 6px 16px;
            border: none;
            border-radius: 4px;
            background: #e74c3c;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }
        .ban-btn:disabled {
            background: #888;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:700px;">
        <h1>Ban des maps</h1>
        <div id="ban-info" style="margin-bottom:18px;">
            Chargement des informations du match...
        </div>
        <div class="map-list" id="map-list"></div>
        <div id="ban-status" style="margin-top:24px;color:#e67e22;"></div>
        <div id="side-picker-info" style="margin-top:18px;color:#27ae60;"></div>
    </div>
    <script>
        const userId = <?= $user_id ?>;
        const matchId = <?= $match_id ?>;
        // Les maps et leurs images
        const maps = {
            'aim_map':         {name: 'Aim Map',         img: '/public/maps/aim_map.jpg'},
            'aim_map_cartoon': {name: 'Aim Map Cartoon', img: '/public/maps/aim_map_cartoon.jpg'},
            'aim_centro':      {name: 'Aim Centro',      img: '/public/maps/aim_centro.jpg'},
            'aim_chess':       {name: 'Aim Chess',       img: '/public/maps/aim_chess.jpg'},
            'awp_lego':        {name: 'Awp Lego',        img: '/public/maps/awp_lego.jpg'}
        };

        let redirecting = false;

        function refreshBanState() {
            fetch('/src/api/ban_map_status.php?match_id=' + matchId)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('ban-info').innerHTML =
                        `<b>${data.player1_name}</b> vs <b>${data.player2_name}</b><br>
                        ${data.finished ? "<span style='color:#27ae60'>La map sélectionnée est : <b>" + (maps[data.selected_map] ? maps[data.selected_map].name : data.selected_map) + "</b></span>"
                        : (data.current_turn == userId ? "<span style='color:#27ae60'>C'est ton tour de bannir !</span>" : "<span style='color:#e67e22'>En attente de l'adversaire...</span>")}`;

                    let html = '';
                    if (data.maps_left && data.maps_left.length > 0) {
                        data.maps_left.forEach(map_id => {
                            html += `<div class="map-card" id="card-${map_id}">
                                <img src="${maps[map_id].img}" alt="${maps[map_id].name}">
                                <div style="font-weight:bold;">${maps[map_id].name}</div>
                                <button class="ban-btn" onclick="banMap('${map_id}')" ${data.current_turn != userId || data.finished ? 'disabled' : ''}>Ban</button>
                            </div>`;
                        });
                    }
                    document.getElementById('map-list').innerHTML = html;

                    if (data.finished) {
                        document.getElementById('ban-status').innerText = "La sélection de map est terminée !";
                        // Affiche le joueur qui choisira le côté si dispo
                        if (data.side_picker_name) {
                            document.getElementById('side-picker-info').innerText = data.side_picker_name + " va choisir le côté.";
                        }
                        // Redirection vers choose_side.php après 2 secondes
                        if (!redirecting) {
                            redirecting = true;
                            setTimeout(() => {
                                window.location.href = "choose_side.php?match_id=" + matchId;
                            }, 2000);
                        }
                    }
                });
        }

        function banMap(mapId) {
            fetch('/src/api/ban_map.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    match_id: matchId,
                    map_id: mapId
                })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('ban-status').innerText = data.message;
                refreshBanState();
            });
        }

        setInterval(refreshBanState, 2000);
        window.onload = refreshBanState;
    </script>
</body>
</html>