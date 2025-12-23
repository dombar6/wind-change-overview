<?php
session_start();
include_once '../backend/db.php';

if (!isset($_SESSION["vartotojo_id"]) || $_SESSION["role"] !== 'super_admin') {
    header("Location: login.php");
    exit();
}
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $query = "DELETE FROM vartotojai WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Vartotojas sėkmingai ištrintas.";
		$_SESSION['message_type'] = "success";
	} else {
        $_SESSION['message'] = "Klaida ištrinant vartotoją.";
		$_SESSION['message_type'] = "error";
    }
}
$query = "SELECT id, vartotojas, epastas, slaptazodis, role FROM vartotojai";
$result = $conn->query($query);

if (!$result) {
    die("Klaida vykdant užklausą: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Super Admino portalas</title>
    <link rel="stylesheet" href="admin_style2.css">
</head>
<body>
	<h1>Super Administratoriaus portalas
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
	<div class="container">
		<button class="add-user" onclick="window.location.href='add_user.php';">Pridėti naują vartotoją</button>
	<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Vartotojas</th>
                <th>E-paštas</th>
                <th>Slaptažodis (Šifruotas)</th>
				<th>Vaidmuo</th>
				<th></th>
            </tr>
        </thead>
        <tbody>
			<?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['vartotojas']); ?></td>
                    <td><?php echo htmlspecialchars($row['epastas']); ?></td>
                    <td><?php echo htmlspecialchars($row['slaptazodis']); ?></td>
					<td><?php echo htmlspecialchars($row['role']); ?></td>
					<td>
                    
					<form method="GET" action="edit_user.php" style="display:inline;">
        				<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        				<button type="submit">Redaguoti</button>
    				</form>
						
                    <form method="POST" action="super_admin_puslapis.php" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" onclick="return confirm('Ar tikrai norite ištrinti šį vartotoją?');">Ištrinti</button>
                    </form>
						
                </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
		</div>
		</div>
</body>
</html>