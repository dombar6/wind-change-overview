<?php
include_once '../backend/db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $query = "SELECT id, slaptazodis, autentifikuotas, role FROM vartotojai WHERE epastas = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $hashed_password, $autentifikuotas, $role);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        if ($autentifikuotas) {
			$_SESSION["vartotojo_id"] = $id;
            $_SESSION["role"] = $role;
        	if ($role == 'admin') {
            header("Location: admin_puslapis.php");
        } else if ($role == 'super_admin') {
            header("Location: super_admin_puslapis.php");
        } else{
            header("Location: profile.php");
        }
        } else {
            $_SESSION['message'] = "Patvirtinkite savo paskyrą per el. paštą.";
			$_SESSION['message_type'] = "info";
        }
    } else {
        $_SESSION['message'] = "Neteisingi prisijungimo duomenys.";
		$_SESSION['message_type'] = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Prisijungimas</title>
    <link rel="stylesheet" href="register_style.css">
</head>
<body>
	<h1>Prisijungimas
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
	<form type="login" method="POST" action="login.php">
    <input type="email" name="email" placeholder="El. paštas" required>
    <input type="password" name="password" placeholder="Slaptažodis" required>
    <button type="submit">Prisijungti</button>
</form>
</body>
</html>