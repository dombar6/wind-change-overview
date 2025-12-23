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

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $query = "SELECT vartotojas, epastas, role FROM vartotojai WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $role);
    $stmt->fetch();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
    $user_id = $_POST['update_id'];
    $username = $_POST['vartotojas'];
    $email = $_POST['epastas'];
    $role = $_SESSION["role"] !== 'super_admin' ? 'user' : $_POST["role"];
	$update_query = "UPDATE vartotojai SET vartotojas = ?, epastas = ?, role = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $username, $email, $role, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Vartotojo duomenys sėkmingai atnaujinti.";
		$_SESSION['message_type'] = "success";
    } else {
		$_SESSION['message'] = "Klaida atnaujinant vartotojo duomenis.";
		$_SESSION['message_type'] = "error";
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Redaguoti vartotoją</title>
    <link rel="stylesheet" href="register_style.css">
</head>
<body>
	<h1>Redaguoti Vartotoją
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
    <form method="POST" action="edit_user.php">
        <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($user_id); ?>">
        <input type="text" name="vartotojas" value="<?php echo htmlspecialchars($username); ?>" required>
        <input type="email" name="epastas" value="<?php echo htmlspecialchars($email); ?>" required>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
			<select name="role" placeholder="Vaidmuo" required>
				<option value="user">Vartotojas</option>
				<option value="admin">Administratorius</option>
			</select>
    	<?php endif; ?>	

        <button type="submit">Atnaujinti</button>
    </form>
</body>
</html>