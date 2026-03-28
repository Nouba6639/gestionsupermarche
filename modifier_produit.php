<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Récupération de l'ID
if (!isset($_GET['id'])) {
    header("Location: produits.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Récupérer le produit à modifier
$stmt = $conn->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produit = $result->fetch_assoc();
$stmt->close();

if (!$produit) {
    echo "Produit introuvable.";
    exit;
}


$categories = [];
$res_cat = $conn->query("SELECT * FROM categories");
while ($row = $res_cat->fetch_assoc()) {
    $categories[] = $row;
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);
    $id_categorie = intval($_POST['id_categorie']);

    $stmt = $conn->prepare("UPDATE produits SET nom = ?, description = ?, prix = ?, quantite = ?, id_categorie = ? WHERE id = ?");
    $stmt->bind_param("ssdiii", $nom, $description, $prix, $quantite, $id_categorie, $id);

    if ($stmt->execute()) {
        header("Location: produits.php");
        exit;
    } else {
        $message = "Erreur lors de la mise à jour : " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le produit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">

<main class="dashboard-content">
    <h1>Modifier le Produit</h1>

    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" class="form-produit">
        <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required>
        <textarea name="description" rows="2"><?= htmlspecialchars($produit['description']) ?></textarea>
        <input type="number" step="0.01" name="prix" value="<?= $produit['prix'] ?>" required>
        <input type="number" name="quantite" value="<?= $produit['quantite'] ?>" required>

        <select name="id_categorie" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $produit['id_categorie']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Enregistrer les modifications</button>
        <a href="produits.php" class="btn-delete" style="text-decoration:none;">Annuler</a>
    </form>
</main>

</body>
</html>
