<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$message = "";

// Récupération des produits disponibles
$produits = [];
$res = $conn->query("SELECT id, nom, quantite FROM produits ORDER BY nom ASC");
while ($row = $res->fetch_assoc()) {
    $produits[] = $row;
}

// Traitement de la vente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = intval($_POST['id_produit']);
    $quantite_vendue = intval($_POST['quantite']);

    // Vérification du stock
    $stmt = $conn->prepare("SELECT quantite FROM produits WHERE id = ?");
    $stmt->bind_param("i", $id_produit);
    $stmt->execute();
    $result = $stmt->get_result();
    $produit = $result->fetch_assoc();
    $stmt->close();

    if ($produit && $produit['quantite'] >= $quantite_vendue) {
        // Enregistrer la vente
        $stmt = $conn->prepare("INSERT INTO ventes (id_produit, quantite) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_produit, $quantite_vendue);
        $stmt->execute();
        $stmt->close();

        // Mettre à jour le stock
        $stmt = $conn->prepare("UPDATE produits SET quantite = quantite - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantite_vendue, $id_produit);
        $stmt->execute();
        $stmt->close();

        $message = "✅ Vente enregistrée avec succès.";
    } else {
        $message = "❌ Stock insuffisant pour cette vente.";
    }
}

// Récupération des ventes récentes
$sql = "
    SELECT v.id, v.quantite, v.date_vente, p.nom AS produit
    FROM ventes v
    JOIN produits p ON v.id_produit = p.id
    ORDER BY v.date_vente DESC
    LIMIT 10
";
$res_ventes = $conn->query($sql);
$ventes = $res_ventes->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ventes - Supermarché</title>
    <link rel="stylesheet" href="css/st.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<script>
    const ctx = document.getElementById('graphVentes').getContext('2d');
    const graphVentes = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Quantité vendue',
                data: <?= json_encode($data) ?>,
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
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
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</aside>

<main class="dashboard-content">
    <h1>Enregistrer une Vente</h1>

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

        <input type="number" name="quantite" placeholder="Quantité vendue" min="1" required>
        <button type="submit">Enregistrer</button>
    </form>

    <h2>📊 Statistiques des Ventes (7 derniers jours)</h2>
    <canvas id="graphVentes" width="600" height="250"></canvas>

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
            <?php foreach ($ventes as $v): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['produit']) ?></td>
                    <td><?= $v['quantite'] ?></td>
                    <td><?= $v['date_vente'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
