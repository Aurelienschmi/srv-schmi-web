<?php
include 'auth.php';
include '../db.php';

// Redirige si l'utilisateur n'est pas connecté ou n'a pas accès à cette page
if (!user_has_access('schmischmi.php')) {
    header('Location: ../acces_refuse.php');
    exit();
}

// Récupère les liens depuis la base
$stmt = $pdo->query('SELECT * FROM liens');
$liens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifie si l'utilisateur a accès à la page admin
$canManageLinks = user_has_access('admin.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Schmischmi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background:rgb(122, 121, 121);
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
            max-width: 520px;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .link-card a.main-link {
            color: #ff8800;
            font-size: 1.15em;
            text-decoration: none;
            font-weight: bold;
            flex: 1;
            text-align: left;
            padding-left: 18px;
        }
        .link-card a.main-link:hover {
            color: #e67600;
            text-decoration: underline;
        }
        .crud-links {
            display: flex;
            gap: 8px;
            margin-right: 18px;
        }
        .crud-links a {
            color: #888;
            font-size: 1.2em;
            text-decoration: none;
            transition: color 0.2s;
        }
        .crud-links a:hover {
            color: #ff8800;
        }
        .add-link-btn {
            display: inline-block;
            margin-top: 10px;
            background: #ff8800;
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1em;
            transition: background 0.2s;
        }
        .add-link-btn:hover {
            background: #e67600;
        }
        @media (max-width: 600px) {
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
        .menu-btn {
            position: absolute;
            left: 24px;
            top: 22px;
            background: #fff;
            color: #ff8800;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: bold;
            font-size: 1em;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            z-index: 10;
            cursor: pointer;
        }
        .menu-btn:hover {
            background: #ffe0b3;
            color: #e67600;
        }
        @media (max-width: 600px) {
            .menu-btn {
                left: 8px;
                top: 10px;
                padding: 6px 10px;
                font-size: 0.95em;
            }
        }
    </style>
</head>
<body>
    <header>
<a href="index.php" class="menu-btn">☰ Menu</a>
        Schmischmi
        <?php if (!empty($_SESSION['user'])): ?>
            <span class="user-info"><?= htmlspecialchars($_SESSION['user']) ?></span>
        <?php endif; ?>
    </header>
    <div class="container">
        <?php foreach ($liens as $lien): ?>
            <div class="link-card">
                <a class="main-link" href="<?= htmlspecialchars($lien['url']) ?>" target="_blank">
                    <?= htmlspecialchars($lien['label']) ?>
                </a>
                <?php if ($canManageLinks): ?>
                    <span class="crud-links">
                        <a href="/schmischmi/edit_link.php?id=<?= $lien['id'] ?>" title="Modifier">✏️</a>
                        <a href="/schmischmi/delete_link.php?id=<?= $lien['id'] ?>" title="Supprimer" onclick="return confirm('Supprimer ce lien ?');">🗑️</a>
                    </span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($canManageLinks): ?>
            <a class="add-link-btn" href="/schmischmi/add_link.php">+ Ajouter un lien</a>
        <?php endif; ?>
    </div>
</body>
</html>