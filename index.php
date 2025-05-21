<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>srv-schmi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background:rgb(122, 121, 121); /* gris plus marqué */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background: #ff8800;
            color: #fff;
            padding: 32px 0 24px 0;
            text-align: center;
            font-size: 2.2em;
            font-weight: bold;
            letter-spacing: 2px;
            position: relative;
        }
        .user-info {
            position: absolute;
            right: 32px;
            top: 18px;
            font-size: 1em;
            font-weight: normal;
            color: #fff;
        }
        .container {
            max-width: 420px;
            margin: 60px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.10);
            padding: 36px 18px 28px 18px;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .link-card {
            background: #f7f7f7;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            padding: 18px 0;
            margin: 0 auto;
            width: 90%;
            transition: box-shadow 0.2s, background 0.2s;
        }
        .link-card:hover {
            background: #ffe0b3;
            box-shadow: 0 2px 8px rgba(255,136,0,0.10);
        }
        .link-card a {
            color: #ff8800;
            font-size: 1.15em;
            text-decoration: none;
            font-weight: bold;
            display: block;
            width: 100%;
            height: 100%;
        }
        .link-card a:hover {
            color: #e67600;
            text-decoration: underline;
        }
        @media (max-width: 500px) {
            header {
                font-size: 1.3em;
                padding: 20px 0 16px 0;
            }
            .user-info {
                right: 12px;
                top: 8px;
                font-size: 0.95em;
            }
            .container {
                max-width: 98vw;
                margin: 18px auto;
                padding: 12px 2vw;
                gap: 12px;
            }
            .link-card {
                padding: 12px 0;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <header>
        srv-schmi
        <?php if (!empty($_SESSION['user'])): ?>
            <span class="user-info"><?= htmlspecialchars($_SESSION['user']) ?></span>
        <?php endif; ?>
    </header>
    <div class="container">
        <div class="link-card"><a href="login.php">Login</a></div>
        <div class="link-card"><a href="csgo.php">CSGO</a></div>
        <div class="link-card"><a href="palworld.php">Palworld</a></div>
        <div class="link-card"><a href="schmischmi.php">Schmischmi</a></div>
        <div class="link-card"><a href="logout.php">Logout</a></div>
    </div>
</body>
</html>