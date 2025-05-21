<?php

include 'db.php';
include 'auth.php';
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
    <style>
        body {
            background: #555353;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .admin-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 500px;
            margin: 60px auto;
            padding: 32px 28px 24px 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            box-sizing: border-box;
            position: relative;
        }
        h1 {
            color: #ff8800;
            text-align: center;
            margin-bottom: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        th, td {
            padding: 8px 6px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            background: #f7f7f7;
        }
        .actions form {
            display: inline;
        }
        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #f7f7f7;
            font-size: 15px;
        }
        button, .btn {
            background: #ff8800;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin-right: 4px;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            background: #e67600;
        }
        .message {
            color: #fff;
            background: #ff8800;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            text-align: center;
        }
        .logout {
            position: absolute;
            top: 20px;
            right: 28px;
            margin: 0;
        }
        .pages-list {
            margin-bottom: 10px;
        }
        .pages-list label {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="logout">
            <a href="logout.php" class="btn">Déconnexion</a>
        </div>
        <h1>Gestion des utilisateurs</h1>
        <p>Bienvenue <?= htmlspecialchars($_SESSION['user']) ?></p>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout ou d'édition -->
        <form method="post" style="margin-bottom:18px;">
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
            <button><?= $editUser ? 'Modifier' : 'Ajouter' ?></button>
            <?php if ($editUser): ?>
                <a href="admin.php" class="btn" style="background:#aaa;">Annuler</a>
            <?php endif; ?>
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
                            <button onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>