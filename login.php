<?php
session_start();
include 'db.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sécurisation des entrées
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Vérifie si les champs ne sont pas vides
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Préparation de la requête
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $user = $res->fetch_assoc();

                // Vérifie que le mot de passe est bien hashé
                if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    $_SESSION['user'] = $user;
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $_SESSION['user'] = $user;
                    header("Location: dashboard.php");
                }
            } else {
                $erreur = "Aucun compte trouvé avec cet email.";
            }

            $stmt->close();
        } else {
            $erreur = "Erreur de préparation de la requête.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Supermarché</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-container">
    <h2>Connexion</h2>
    <?php if (!empty($erreur)) echo "<p class='erreur'>$erreur</p>"; ?>
    <form method="post" action="login.php">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Mot de passe</label>
        <div class="password-container">
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>
            <button type="button" onclick="togglePassword()">👁️</button>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>

<script src="js/script.js"></script>
</body>
</html>
