<?php
include_once __DIR__ . '/../../config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT game_name, profile_image FROM game_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_image_url = !empty($profile['profile_image'])
    ? "/public/profiles/" . $profile['profile_image']
    : "/public/profiles/default_profile.png";
$game_name = !empty($profile['game_name']) ? htmlspecialchars($profile['game_name']) : htmlspecialchars($_SESSION['user']);
$error = '';
$success = '';

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_game_name = trim($_POST['game_name'] ?? '');
    $new_profile_image = $profile['profile_image'];

    if (empty($new_game_name)) {
        $error = "Le pseudo de jeu est obligatoire.";
    } else {
        // Gestion de l'upload d'image si une nouvelle image est envoyée
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
                        $new_profile_image = $filename;
                    } else {
                        $error = "Erreur lors de l'upload de l'image.";
                    }
                } else {
                    $error = "Format d'image non supporté.";
                }
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE game_profiles SET game_name = ?, profile_image = ? WHERE user_id = ?");
        $stmt->execute([$new_game_name, $new_profile_image, $user_id]);
        $success = "Profil mis à jour avec succès !";
        // Recharge les nouvelles infos
        $profile_image_url = !empty($new_profile_image)
            ? "/public/profiles/" . $new_profile_image
            : "/public/profiles/default_profile.png";
        $game_name = htmlspecialchars($new_game_name);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon compte</title>
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
            }
        }
    </script>
</head>
<body>
    <div class="admin-container schmischmi-container" style="max-width:400px;">
        <h1>Mon compte</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="create-profile-form">
            <div style="text-align:center;">
                <img id="img-preview" src="<?= $profile_image_url ?>" alt="Profil" class="profile-img-preview" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #888;">
            </div>
            <label for="game_name">Pseudo de jeu :</label>
            <input type="text" name="game_name" id="game_name" maxlength="50" required autocomplete="off" value="<?= htmlspecialchars($game_name) ?>">

            <label for="profile_image">Changer l'image de profil :</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="previewImage(this)">
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
        <div style="margin-top:24px;">
            <a href="csgo.php" class="btn">Retour CS:GO</a>
        </div>
    </div>
</body>
</html>