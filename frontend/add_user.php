<?php
session_start();
include_once '../backend/db.php';

if ((!isset($_SESSION["vartotojo_id"]) && $_SESSION["role"] !== 'admin') || (!isset($_SESSION["vartotojo_id"]) && $_SESSION["role"] !== 'super_admin') || !isset($_SESSION["vartotojo_id"])) {
    header("Location: login.php");
    exit();
}
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["vartotojas"];
    $email = $_POST["epastas"];
    $password = password_hash($_POST["slaptazodis"], PASSWORD_BCRYPT);
    $role = $_SESSION["role"] !== 'super_admin' ? 'user' : $_POST["role"];

    $query = "INSERT INTO vartotojai (vartotojas, epastas, slaptazodis, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $username, $email, $password, $role);
	if ($stmt->execute()) {
        $_SESSION['message'] = "Naujas vartotojas sėkmingai pridėtas.";
		$_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Klaida pridedant vartotoją.";
		$_SESSION['message_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Pridėti naują vartotoją</title>
    <link rel="stylesheet" href="register_style.css">
</head>
	<body>
    <h1>Pridėti naują vartotoją
	<?php if ($_SESSION['role'] === 'super_admin'): ?>
		<button class="back-btn" onclick="window.location.href='super_admin_puslapis.php';">Grįžti</button>
    <?php endif; ?>
	<?php if ($_SESSION['role'] === 'admin'): ?>
		<button class="back-btn" onclick="window.location.href='admin_puslapis.php';">Grįžti</button>
	<?php endif; ?>
	</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?php echo $_SESSION['message_type']; ?>">
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>
		<form method="POST" action="add_user.php">
        <input type="text" name="vartotojas" placeholder="Vartotojo vardas" required>
        <input type="email" name="epastas" placeholder="El. paštas" required>
        <input type="password" name="slaptazodis" placeholder="Slaptažodis" required>
		
		<?php if ($_SESSION['role'] === 'super_admin'): ?>
			<select name="role" placeholder="Vaidmuo" required>
				<option value="user">Vartotojas</option>
				<option value="admin">Administratorius</option>
			</select>
    	<?php endif; ?>	
		
        
			<button type="submit">Pridėti</button>
    </form>
</body>
</html>