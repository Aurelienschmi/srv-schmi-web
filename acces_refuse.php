<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès refusé</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: rgb(122, 121, 121);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .center-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.10);
            padding: 48px 32px 36px 32px;
            text-align: center;
            max-width: 400px;
        }
        .center-box h1 {
            color: #ff8800;
            margin-bottom: 18px;
            font-size: 2em;
        }
        .center-box p {
            font-size: 1.2em;
            margin-bottom: 18px;
        }
        .countdown {
            font-size: 1.5em;
            color: #ff8800;
            font-weight: bold;
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
    <script>
        let seconds = 10;
        function updateCountdown() {
            document.getElementById('countdown').textContent = seconds;
            if (seconds > 0) {
                seconds--;
                setTimeout(updateCountdown, 1000);
            } else {
                window.location.href = "index.php";
            }
        }
        window.onload = updateCountdown;
    </script>
</head>
<body>
    <div class="center-box">
        <h1>Accès refusé</h1>
        <p>Vous n'avez pas l'autorisation d'accéder à cette page.</p>
        <p>Retour à l'accueil dans <span class="countdown" id="countdown">10</span> secondes...</p>
        <a href="index.php" class="home-btn">Retour à l'accueil</a>
    </div>
</body>
</html>