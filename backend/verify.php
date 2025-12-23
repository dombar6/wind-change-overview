<?php
include_once 'db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $query = "UPDATE vartotojai SET autentifikuotas = 1 WHERE autentifikacijos_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);

    if ($stmt->execute()) {
        echo "Jūsų paskyra patvirtinta!";
    } else {
        echo "Patvirtinimas nepavyko.";
    }
}
?>
