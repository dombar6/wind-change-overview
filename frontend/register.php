<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load()

include_once '../backend/db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["vartotojas"];
    $email = $_POST["epastas"];
    $password = password_hash($_POST["slaptazodis"], PASSWORD_DEFAULT);
	$role = 'user';
    $token = bin2hex(random_bytes(32));

    // Įrašome vartotoją į duomenų bazę
    $query = "INSERT INTO vartotojai (vartotojas, epastas, slaptazodis, autentifikacijos_token, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $username, $email, $password, $token, $role);

     if ($stmt->execute()) {
		 $mail = new PHPMailer(true);
        try {
			$mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'apikey';
            $mail->Password = $_ENV['SEND_GRID_API_KEY'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
			
			$mail->setFrom('dbartkievicius@gmail.com', 'Buriuotojai');
            $mail->addAddress($email);
			
			$mail->isHTML(true);
            $mail->Subject = 'Patvirtinkite savo paskyra';
            $verification_link = "http://localhost/verify.php?token=$token";
            $mail->Body = "Paspauskite šią nuorodą, kad patvirtintumėte savo paskyrą: <a href='$verification_link'>Patvirtinimo nuoroda</a>";
			
			$mail->send();
            $_SESSION['message'] = "Registracija sėkminga! Patvirtinimo laiškas išsiųstas į jūsų el. paštą.";
			$_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $_SESSION['message'] = "Nepavyko išsiųsti el. laiško: {$mail->ErrorInfo}";
			$_SESSION['message_type'] = "error";
        }
    } else {
         $_SESSION['message'] = "Klaida: registracija nepavyko.";
		 $_SESSION['message_type'] = "error";
	}
}
?>


<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Registracija</title>
    <link rel="stylesheet" href="register_style.css">
</head>
<body>
	<h1>Registracija
	<button class="back-btn" onclick="window.location.href='index.php';">Grįžti</button>
	</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?php echo $_SESSION['message_type']; ?>">
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>
	<form method="POST" action="register.php">
        <input type="text" name="vartotojas" placeholder="Vartotojo vardas" required>
        <input type="email" name="epastas" placeholder="El. paštas" required>
        <input type="password" name="slaptazodis" placeholder="Slaptažodis" required>
        <button type="submit">Registruotis</button>
    </form>
</body>
</html>