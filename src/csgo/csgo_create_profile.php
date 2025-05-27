<?php
include_once __DIR__ . '/../../config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pdo = get_db_connection();
$error = '';
$success = '';

// Vérifie si le profil existe déjà
$stmt = $pdo->prepare("SELECT id FROM game_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
if ($stmt->fetch()) {
    header("Location: csgo.php");
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_name = trim($_POST['game_name'] ?? '');
    $profile_image = null;

    if (empty($game_name)) {
        $error = "Le pseudo de jeu est obligatoire.";
    } else {
        // Gestion de l'upload d'image
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $maxSize = 1024 * 1024; // 1 Mo
    if ($_FILES['profile_image']['size'] > $maxSize) {
        $error = "L'image ne doit pas dépasser 1 Mo.";
    } else {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $targetDir = __DIR__ . "/../../public/profiles/";
            $target = $targetDir . $filename;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                $profile_image = $filename;
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $error = "Format d'image non supporté.";
        }
    }
}

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO game_profiles (user_id, game_name, profile_image) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $game_name, $profile_image]);
            $success = "Profil créé avec succès !";
            header("Location: csgo.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer mon profil CS:GO</title>
    <link rel="stylesheet" href="/public/styles.css">
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('img-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Créer mon profil CS:GO</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
<form method="post" enctype="multipart/form-data" class="create-profile-form">
    <label for="game_name">Pseudo de jeu :</label>
    <input type="text" name="game_name" id="game_name" maxlength="50" required autocomplete="off">

    <div class="profile-image-upload">
        <label for="profile_image" class="custom-file-label">
            <span id="file-label-text">Choisir une image</span>
        </label>
        <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="previewImage(this)" max-size="1048576">
        <img id="img-preview" class="profile-img-preview" alt="Aperçu de l'image de profil">
    </div>

    <button type="submit" class="btn">Créer mon profil</button>
</form>
        <a href="../index.php" class="btn home-btn" style="margin-top:18px;">Retour à l'accueil</a>
    </div>
</body>
</html>