<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$message = "";

// Récupérer tous les produits
$produits = [];
$res = $conn->query("SELECT id, nom, quantite FROM produits ORDER BY nom ASC");
while ($row = $res->fetch_assoc()) {
    $produits[] = $row;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = intval($_POST['id_produit']);
    $quantite = intval($_POST['quantite']);

    if ($quantite > 0) {
        // Enregistrer la commande
        $stmt = $conn->prepare("INSERT INTO commandes (id_produit, quantite) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_produit, $quantite);
        $stmt->execute();
        $stmt->close();

        // Mettre à jour le stock
        $stmt = $conn->prepare("UPDATE produits SET quantite = quantite + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantite, $id_produit);
        $stmt->execute();
        $stmt->close();

        $message = "✅ Commande enregistrée et stock mis à jour.";
    } else {
        $message = "❌ Quantité invalide.";
    }
}

// Récupérer les commandes récentes
$sql = "
    SELECT c.id, c.quantite, c.date_commande, p.nom AS produit
    FROM commandes c
    JOIN produits p ON c.id_produit = p.id
    ORDER BY c.date_commande DESC
    LIMIT 10
";
$res_commandes = $conn->query($sql);
$commandes = $res_commandes->fetch_all(MYSQLI_ASSOC);
// Commandes des 7 derniers jours
$cmd_labels = [];
$cmd_data = [];

$sqlGraphCmd = "
    SELECT date_commande, SUM(quantite) AS total
    FROM commandes
    WHERE date_commande >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY date_commande
    ORDER BY date_commande ASC
";
$resGraphCmd = $conn->query($sqlGraphCmd);
while ($row = $resGraphCmd->fetch_assoc()) {
    $cmd_labels[] = $row['date_commande'];
    $cmd_data[] = $row['total'];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - Supermarché</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<script>
const ctxCmd = document.getElementById('cmdChart').getContext('2d');
const cmdChart = new Chart(ctxCmd, {
    type: 'line',
    data: {
        labels: <?= json_encode($cmd_labels) ?>,
        datasets: [{
            label: 'Quantité commandée',
            data: <?= json_encode($cmd_data) ?>,
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

<body class="dashboard-body">

<aside class="sidebar">
    <h2>🛒 Supermarché</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produits.php">Produits</a></li>
        <li><a href="ventes.php">Ventes</a></li>
        <li><a href="commandes.php" class="active">Commandes</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</aside>

<main class="dashboard-content">
    <h1>📦 Nouvelle Commande</h1>

    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" class="form-vente">
        <select name="id_produit" required>
            <option value="">-- Sélectionner un produit --</option>
            <?php foreach ($produits as $p): ?>
                <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nom']) ?> (Stock: <?= $p['quantite'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="quantite" placeholder="Quantité commandée" min="1" required>
        <button type="submit">Valider la commande</button>
    </form>

    <h2>📈 Commandes des 7 derniers jours</h2>
    <canvas id="cmdChart" width="600" height="250"></canvas>

    <table class="table-ventes">
        <thead>
            <tr>
                <th>ID</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commandes as $cmd): ?>
                <tr>
                    <td><?= $cmd['id'] ?></td>
                    <td><?= htmlspecialchars($cmd['produit']) ?></td>
                    <td><?= $cmd['quantite'] ?></td>
                    <td><?= $cmd['date_commande'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
