<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// Ajouter un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $email, $mot_de_passe, $role);

    if ($stmt->execute()) {
        $message = "✅ Utilisateur ajouté avec succès.";
    } else {
        $message = "❌ Erreur lors de l'ajout : " . $stmt->error;
    }
    $stmt->close();
}

// Supprimer un utilisateur
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);

    if ($id != $_SESSION['user']['id']) { // éviter qu'un admin se supprime lui-même
        $conn->query("DELETE FROM utilisateurs WHERE id = $id AND role != 'admin'");
        $message = "🗑️ Utilisateur supprimé.";
    }
}

// Lister les utilisateurs
$result = $conn->query("SELECT * FROM utilisateurs ORDER BY id DESC");
$utilisateurs = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">

<aside class="sidebar">
    <h2>🛒 Supermarché</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produits.php">Produits</a></li>
        <li><a href="ventes.php">Ventes</a></li>
        <li><a href="commandes.php">Commandes</a></li>
        <li><a href="utilisateurs.php" class="active">Utilisateurs</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</aside>

<main class="dashboard-content">
    <h1>👤 Utilisateurs</h1>

    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" class="form-vente">
        <input type="text" name="nom" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        <select name="role" required>
            <option value="caissier">Caissier</option>
            <option value="admin">Administrateur</option>
        </select>
        <button type="submit" name="ajouter">Ajouter</button>
    </form>

    <h2>📋 Liste des Utilisateurs</h2>
    <table class="table-ventes">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['role'] ?></td>
                    <td>
                        <a href="?supprimer=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
