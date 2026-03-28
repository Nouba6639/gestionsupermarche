<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$message = "";

// Récupération des catégories pour le select
$categories = [];
$res_cat = $conn->query("SELECT * FROM categories");
while ($row = $res_cat->fetch_assoc()) {
    $categories[] = $row;
}

// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);
    $id_categorie = intval($_POST['id_categorie']);

    $stmt = $conn->prepare("INSERT INTO produits (nom, description, prix, quantite, id_categorie) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdii", $nom, $description, $prix, $quantite, $id_categorie);

    if ($stmt->execute()) {
        $message = "Produit ajouté avec succès ✅";
    } else {
        $message = "Erreur : " . $stmt->error;
    }
    $stmt->close();
}

// Lister les produits avec nom de la catégorie
$query = "
    SELECT p.id, p.nom, p.description, p.prix, p.quantite, c.nom AS categorie
    FROM produits p
    JOIN categories c ON p.id_categorie = c.id
    ORDER BY p.id DESC
";
$res = $conn->query($query);
$produits = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Produits - Supermarché</title>
    <link rel="stylesheet" href="css/sty.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">

<aside class="sidebar">
    <h2>🛒 Supermarché</h2>
    <ul>
        <li><a href="dashboard.php">Tableau de bord</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</aside>

<main class="dashboard-content">
    <h1>Gestion des Produits</h1>

    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" class="form-produit">
        <input type="text" name="nom" placeholder="Nom du produit" required>
        <textarea name="description" placeholder="Description" rows="2"></textarea>
        <input type="number" step="0.01" name="prix" placeholder="Prix (FCFA)" required>
        <input type="number" name="quantite" placeholder="Quantité" required>
        
        <select name="id_categorie" required>
            <option value="">-- Catégorie --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Ajouter</button>
    </form>

    <table class="table-produits">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th>Prix (FCFA)</th>
                <th>Quantité</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= nl2br(htmlspecialchars($p['description'])) ?></td>
                    <td><?= htmlspecialchars($p['categorie']) ?></td>
                    <td><?= number_format($p['prix'], 2) ?></td>
                    <td><?= $p['quantite'] ?></td>
                    <td>
                        <a href="modifier_produit.php?id=<?= $p['id'] ?>" class="btn-edit">✏️ Modifier</a>
                        <a href="supprimer_produits.php?id=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ce produit ?')">🗑 Supprimer</a>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
