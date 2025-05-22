<?php
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../config/auth.php';
if (!user_has_access('admin.php')) {
    header('Location: acces_refuse.php');
    exit();
}
// Récupérer la liste des pages
$pages = $pdo->query("SELECT * FROM pages ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Gestion des actions CRUD
$message = '';
if (isset($_POST['action'])) {
    // Ajouter un utilisateur
    if ($_POST['action'] === 'add' && !empty($_POST['username']) && !empty($_POST['password'])) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, SHA2(?, 256))");
        if ($stmt->execute([$_POST['username'], $_POST['password']])) {
            $userId = $pdo->lastInsertId();
            // Gérer les droits d'accès
            if (!empty($_POST['pages'])) {
                $stmt2 = $pdo->prepare("INSERT INTO user_pages (user_id, page_id) VALUES (?, ?)");
                foreach ($_POST['pages'] as $pageId) {
                    $stmt2->execute([$userId, $pageId]);
                }
            }
            $message = "Utilisateur ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout.";
        }
        
    }
    // Modifier un utilisateur
    if ($_POST['action'] === 'edit' && !empty($_POST['id']) && !empty($_POST['username'])) {
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=SHA2(?, 256) WHERE id=?");
            $stmt->execute([$_POST['username'], $_POST['password'], $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=? WHERE id=?");
            $stmt->execute([$_POST['username'], $_POST['id']]);
        }
        // Met à jour les droits d'accès
        $userId = $_POST['id'];
        $pdo->prepare("DELETE FROM user_pages WHERE user_id=?")->execute([$userId]);
        if (!empty($_POST['pages'])) {
            $stmt2 = $pdo->prepare("INSERT INTO user_pages (user_id, page_id) VALUES (?, ?)");
            foreach ($_POST['pages'] as $pageId) {
                $stmt2->execute([$userId, $pageId]);
            }
        }
        $message = "Utilisateur modifié.";
    }
    // Supprimer un utilisateur
    if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$_POST['id']]);
        $message = "Utilisateur supprimé.";
    }
}

// Récupérer la liste des utilisateurs
$users = $pdo->query("SELECT id, username FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Pour l'édition
$editUser = null;
$userPages = [];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les pages autorisées pour cet utilisateur
    $stmt = $pdo->prepare("SELECT page_id FROM user_pages WHERE user_id=?");
    $stmt->execute([$editUser['id']]);
    $userPages = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'page_id');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion des utilisateurs</title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <a href="index.php" class="btn">Accueil</a>
            </div>
            <div class="logout">
                <a href="logout.php" class="btn">Déconnexion</a>
            </div>
        </div>
        <h1>Gestion des utilisateurs</h1>
        <p>Bienvenue <strong><?= htmlspecialchars($_SESSION['user']) ?></strong></p>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

<form method="post" style="text-align:center;">
    <?php if ($editUser): ?>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
        <input type="text" name="username" placeholder="Nom d'utilisateur" value="<?= htmlspecialchars($editUser['username']) ?>" required>
        <input type="password" name="password" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)">
    <?php else: ?>
        <input type="hidden" name="action" value="add">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
    <?php endif; ?>
    <div class="pages-list-wrapper">
        <div class="pages-list">
            <strong>Pages autorisées :</strong><br>
            <?php foreach ($pages as $page): ?>
                <label>
                    <input type="checkbox" name="pages[]" value="<?= $page['id'] ?>"
                        <?= in_array($page['id'], $userPages) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($page['name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div>
        <button class="btn" style="margin-top:18px;"><?= $editUser ? 'Modifier' : 'Ajouter' ?></button>
        <?php if ($editUser): ?>
            <a href="admin.php" class="btn" style="background:#aaa;">Annuler</a>
        <?php endif; ?>
    </div>
</form>

        <!-- Tableau des utilisateurs -->
        <table>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td class="actions">
                        <a href="admin.php?edit=<?= $user['id'] ?>" class="btn">Modifier</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button class="btn" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>