<?php
session_start();
include_once '../backend/db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

if (!isset($_SESSION["vartotojo_id"]) || $_SESSION["role"] !== 'user') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["vartotojo_id"];

$regions = [
    "Vilnius", 
    "Kaunas", 
    "Klaipėda", 
    "Šiauliai", 
    "Panevėžys", 
    "Alytus", 
    "Marijampolė", 
    "Utena", 
    "Tauragė", 
    "Telšiai"
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_settings"])) {
    $region = $_POST["regionas"];
    $wind_strength_from = !empty($_POST["vejo_stiprumas_nuo"]) ? $_POST["vejo_stiprumas_nuo"] : 0;
    $wind_strength_to = !empty($_POST["vejo_stiprumas_iki"]) ? $_POST["vejo_stiprumas_iki"] : 99999;
    $wind_direction = $_POST["vejo_kryptis"];
	
	if ($wind_strength_from !== null && $wind_strength_to !== null && $wind_strength_from > $wind_strength_to) {
        $_SESSION['message'] = "'Vėjo stiprumas nuo' negali būti didesnis už 'Vėjo stiprumas iki'.";
        $_SESSION['message_type'] = "error";
        header("Location: profile.php");
        exit();
    }
	
	if ((!isset($_POST['vejo_stiprumas_nuo']) && !isset($_POST['vejo_stiprumas_iki'])) 
        || ($_POST['vejo_stiprumas_nuo'] === '' && $_POST['vejo_stiprumas_iki'] === '')) {
        $_SESSION['message'] = "Turite užpildyti bent vieną iš laukų: 'Vėjo stiprumas nuo' arba 'Vėjo stiprumas iki'.";
        $_SESSION['message_type'] = "error";
        header("Location: profile.php");
        exit();
    }
	
	$query = "INSERT INTO pranesimai (vartotojo_id, regionas, vejo_stiprumas_nuo, vejo_stiprumas_iki, vejo_kryptis) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdds", $user_id, $region, $wind_strength_from, $wind_strength_to, $wind_direction);
	if ($stmt->execute()) {
        $_SESSION['message'] = "Pranešimo nustatymai sėkmingai išsaugoti.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Klaida: nepavyko išsaugoti pranešimų nustatymų.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: profile.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];
    $delete_query = "DELETE FROM pranesimai WHERE id = ? AND vartotojo_id = ?";
    $stmt_delete = $conn->prepare($delete_query);
	if (!$stmt_delete) {
        $_SESSION['message'] = "Statement preparation failed: " . $conn->error;
        $_SESSION['message_type'] = "error";
    } else {
        $stmt_delete->bind_param("ii", $delete_id, $user_id);
        if ($stmt_delete->execute()) {
            $_SESSION['message'] = "Pranešimas sėkmingai ištrintas.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Klaida: nepavyko ištrinti pranešimo.";
            $_SESSION['message_type'] = "error";
        }
    }
	header("Location: profile.php");
    exit();
}

$saved_query = "SELECT id, regionas, vejo_stiprumas_nuo, vejo_stiprumas_iki, vejo_kryptis FROM pranesimai WHERE vartotojo_id = ?";
$stmt_saved = $conn->prepare($saved_query);
$stmt_saved->bind_param("i", $user_id);
$stmt_saved->execute();
$saved_results = $stmt_saved->get_result();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Vėjo stebėjimo nustatymai</title>
    <link rel="stylesheet" href="profile_style2.css">
</head>
<body>
    <h1>Norimo pranešimo nustatymas
	<button class="graph" onclick="window.location.href='wind_rose_graph.php';">Grafikas</button>
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
	<!-- Forma naujiems nustatymams įvesti -->
	 <div class="container">
		 <div class="form-container">
    <form method="POST" action="profile.php">
        <input type="hidden" name="save_settings" value="1">
        <select name="regionas" required>
            <option value="" disabled selected>Pasirinkite regioną</option>
            <?php foreach ($regions as $region): ?>
                <option value="<?php echo htmlspecialchars($region); ?>">
                    <?php echo htmlspecialchars($region); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="vejo_stiprumas_nuo" placeholder="Vėjo stiprumas nuo" step="0.1" min="0" max="1000">
        <input type="number" name="vejo_stiprumas_iki" placeholder="Vėjo stiprumas iki" step="0.1" min="0" max="1000">
        <select name="vejo_kryptis" id="vejo_kryptis" required>
        	<option value="" disabled selected>Pasirinkite vėjo kryptį</option>
        	<option value="Šiaurė">Šiaurė</option>
        	<option value="Pietūs">Pietūs</option>
        	<option value="Rytai">Rytai</option>
			<option value="Vakarai">Vakarai</option>
        	<option value="Šiaurės Rytai">Šiaurės Rytai</option>
        	<option value="Šiaurės Vakarai">Šiaurės Vakarai</option>
        	<option value="Pietryčiai">Pietryčiai</option>
        	<option value="Pietvakariai">Pietvakariai</option>
    	</select>
        <button type="submit">Išsaugoti</button>
    </form>
			 </div>
    <!-- Lentelė, rodanti išsaugotus parametrus -->
		 <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Regionas</th>
                <th>Vėjo stiprumo rėžis (m/s)</th>
                <th>Vėjo kryptis</th>
				<th></th>
            </tr>
        </thead>
        <tbody>
			<?php while ($row = $saved_results->fetch_assoc()):
                $vejo_rezis = "";
                if (!empty($row['vejo_stiprumas_nuo']) && !empty($row['vejo_stiprumas_iki']) && $row['vejo_stiprumas_iki'] < 99999) {
                    $vejo_rezis = $row['vejo_stiprumas_nuo'] . "–" . $row['vejo_stiprumas_iki'];
                } elseif (!empty($row['vejo_stiprumas_nuo'])) {
					$vejo_rezis = "≥" . $row['vejo_stiprumas_nuo'];
                } elseif (!empty($row['vejo_stiprumas_iki'])) {
                    $vejo_rezis = "0–" . $row['vejo_stiprumas_iki'];
                }else {
        			$vejo_rezis = "<span style='color:red;'>Klaida: nėra įvestų vėjo stiprumo reikšmių</span>";
    			}
        	?>
                <tr>
                    <td><?php echo htmlspecialchars($row['regionas']); ?></td>
                    <td><?php echo htmlspecialchars($vejo_rezis); ?></td>
                    <td><?php echo htmlspecialchars($row['vejo_kryptis']); ?></td>
                    <td>
						<!-- Ištrynimo forma kiekvienam pranešimui -->
                        <form method="POST" action="profile.php" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" onclick="return confirm('Ar tikrai norite ištrinti šį pranešimą?');">Ištrinti</button>
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