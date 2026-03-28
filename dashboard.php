<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
?>

<?php

$user = $_SESSION['user'];

include 'db.php';

// Total produits
$res_produits = $conn->query("SELECT COUNT(*) AS total FROM produits");
if ($res_produits) {
    $total_produits = $res_produits->fetch_assoc()['total'];
} else {
    echo "Erreur produits : " . $conn->error;
    $total_produits = 0;
}

// Ventes du jour
$aujourdhui = date('Y-m-d');
$res_ventes = $conn->prepare("SELECT COUNT(*) AS total FROM ventes WHERE date_vente = ?");
if ($res_ventes) {
    $res_ventes->bind_param("s", $aujourdhui);
    $res_ventes->execute();
    $result_ventes = $res_ventes->get_result();
    $total_ventes = $result_ventes->fetch_assoc()['total'];
} else {
    echo "Erreur ventes : " . $conn->error;
    $total_ventes = 0;
}

// Commandes en attente
$res_commandes = $conn->query("SELECT COUNT(*) AS total FROM commandes WHERE statut = 'en attente'");
if ($res_commandes) {
    $total_commandes = $res_commandes->fetch_assoc()['total'];
} else {
    echo "Erreur commandes : " . $conn->error;
    $total_commandes = 0;
}

// Utilisateurs
$res_users = $conn->query("SELECT COUNT(*) AS total FROM utilisateurs");
if ($res_users) {
    $total_users = $res_users->fetch_assoc()['total'];
} else {
    echo "Erreur utilisateurs : " . $conn->error;
    $total_users = 0;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Supermarché</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">

    <aside class="sidebar">
        <h2>🛒 Supermarché</h2>
        <ul>
            <li><a href="dashboard.php">Tableau de bord</a></li>
            <li><a href="produits.php">Produits</a></li>
            <li><a href="ventes.php">Ventes</a></li>
            <li><a href="commandes.php">Commandes</a></li>
            <li><a href="utilisateurs.php">Utilisateurs</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </aside>

    <main class="dashboard-content">
        <h1>Bienvenue, <?= htmlspecialchars($user['nom']) ?> 👋</h1>

        <div class="cards">
            <div class="card">
                <h3>Total Produits</h3>
                <p><?= $total_produits ?></p>
            </div>
            <div class="card">
                <h3>Ventes du jour</h3>
                <p><?= $total_ventes ?></p>
            </div>
            <div class="card">
                <h3>Commandes en attente</h3>
                <p><?= $total_commandes ?></p>
            </div>
            <div class="card">
                <h3>Utilisateurs</h3>
                <p><?= $total_users ?></p>
            </div>
        </div>
    </main>

</body>
</html>
