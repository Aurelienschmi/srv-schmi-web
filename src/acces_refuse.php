<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès refusé</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/styles.css">
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
    <div class="admin-container schmischmi-container" style="max-width:400px;text-align:center;">
        <h1>Accès refusé</h1>
        <p>Vous n'avez pas l'autorisation d'accéder à cette page.</p>
        <p>Retour à l'accueil dans <span class="countdown" id="countdown">10</span> secondes...</p>
        <a href="index.php" class="btn" style="width:90%;margin-top:18px;">Retour à l'accueil</a>
    </div>
</body>
</html>