<?php
include_once __DIR__ . '/../../config/auth.php';
include_once __DIR__ . '/../../config/db.php';

// Redirige si l'utilisateur n'est pas connecté ou n'a pas accès à cette page
if (!user_has_access('schmischmi.php')) {
    header('Location: ../acces_refuse.php');
    exit();
}
// Récupère les liens depuis la base
$stmt = $pdo->query('SELECT * FROM liens');
$liens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifie si l'utilisateur a le rôle admin dans la base
$canManageLinks = false;
if (!empty($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $role = $stmt->fetchColumn();
    if ($role === 'admin') {
        $canManageLinks = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Schmischmi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="admin-container schmischmi-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <a href="/src/index.php" class="btn">Accueil</a>
            </div>
            <div>
                <?php if (!empty($_SESSION['user'])): ?>
                    <span class="user-info"><?= htmlspecialchars($_SESSION['user']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <h1>Schmischmi</h1>
        <div class="schmischmi-links">
            <?php foreach ($liens as $lien): ?>
                <div class="link-card">
                    <a class="main-link" href="<?= htmlspecialchars($lien['url']) ?>" target="_blank">
                        <?= htmlspecialchars($lien['label']) ?>
                    </a>
                    <?php if ($canManageLinks): ?>
                        <span class="crud-links">
                            <a href="/src/schmischmi/edit_link.php?id=<?= $lien['id'] ?>" title="Modifier">✏️</a>
                            <a href="/src/schmischmi/delete_link.php?id=<?= $lien['id'] ?>" title="Supprimer" onclick="return confirm('Supprimer ce lien ?');">🗑️</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($canManageLinks): ?>
            <a class="btn add-link-btn" href="/src/schmischmi/add_link.php" style="margin-top:18px;">+ Ajouter un lien</a>
        <?php endif; ?>
    </div>
</body>
</html>