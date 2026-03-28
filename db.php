<?php
$conn = new mysqli("localhost", "root", "", "supermarche");
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}
?>
