<?php
include_once("../backend/pChart.class.php");
include_once("../backend/pData.class.php");

session_start();
include_once '../backend/db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

if (!isset($_SESSION["vartotojo_id"]) || $_SESSION["role"] !== 'user') {
    header("Location: login.php");
    exit();
}

$chartFile = null;
$region = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["region"])) {
    $region = $_POST["region"];
	$query = "SELECT vejo_kryptis, COUNT(*) as count FROM regiono_duomenys WHERE regionas = ? GROUP BY vejo_kryptis";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("s", $region);
	$stmt->execute();
	$result = $stmt->get_result();

	$wind_counts = [
		"Šiaurė" => 0, "Šiaurės Rytai" => 0, "Rytai" => 0, "Pietryčiai" => 0,
		"Pietūs" => 0, "Pietvakariai" => 0, "Vakarai" => 0, "Šiaurės Vakarai" => 0
	];

	while ($row = $result->fetch_assoc()) {
		$wind_counts[$row['vejo_kryptis']] = $row['count'];
	}

	$max_count = max($wind_counts);
    $normalized_counts = [];
    $max_radius = 80;
    foreach ($wind_counts as $direction => $count) {
        $normalized_counts[$direction] = ($count / $max_count) * ($max_radius-3);
    }

	$MyData = new pData();

	$labels = array_keys($normalized_counts);
	$values = array_values($normalized_counts);

	$MyData->AddPoint($values, "WindCounts");
	$MyData->AddPoint($labels, "Labels");
	$MyData->AddSerie("WindCounts");
	$MyData->SetAbsciseLabelSerie("Labels");
	$myPicture = new pChart(600, 600);
	$myPicture->drawFilledRectangle(0, 0, 600, 600, 255, 255, 255);
	$myPicture->drawRectangle(0, 0, 599, 599, 0, 0, 0);
	$myPicture->setFontProperties("/var/www/html/verdana.ttf", 10);
	$myPicture->setGraphArea(50, 50, 550, 550);
	$myPicture->drawGraphAreaGradient(200, 200, 200, 20);
	$myPicture->drawFilledRoundedRectangle(30, 30, 560, 560, 10, 255, 255, 255);
	$myPicture->drawRadarAxis($MyData->GetData(), $MyData->GetDataDescription(), true, 10, 60, 60, 60, 200, 200, 200, $max_count);
	$myPicture->drawFilledRadar($MyData->GetData(), $MyData->GetDataDescription(), 50, false, $max_radius);
	$myPicture->drawTitle(150, 20, $region, 0, 0, 0);

	$chartFile = "/var/www/html/wind_rose.png"; // Save path
	$myPicture->Render($chartFile);
	$stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vėjų rožės grafikas</title>
	<link rel="stylesheet" href="wind_rose_style.css">
</head>
<body>
	<h1>Nurodyto regiono vėjo rožės grafikas
	<button class="back-btn" onclick="window.location.href='profile.php';">Grįžti</button>
	</h1>
    
    <form action="wind_rose_graph.php" method="POST">
        <select name="region" id="region">
			<option value="" disabled selected>Pasirinkite regioną</option>
			<option value="Vilnius" <?php if($region == 'Vilnius') echo 'selected'; ?>>Vilnius</option>
            <option value="Kaunas" <?php if($region == 'Kaunas') echo 'selected'; ?>>Kaunas</option>
            <option value="Klaipėda" <?php if($region == 'Klaipėda') echo 'selected'; ?>>Klaipėda</option>
            <option value="Šiauliai" <?php if($region == 'Šiauliai') echo 'selected'; ?>>Šiauliai</option>
			<option value="Panevėžys" <?php if($region == 'Panevėžys') echo 'selected'; ?>>Panevėžys</option>
            <option value="Alytus" <?php if($region == 'Alytus') echo 'selected'; ?>>Alytus</option>
            <option value="Marijampolė" <?php if($region == 'Marijampolė') echo 'selected'; ?>>Marijampolė</option>
            <option value="Utena" <?php if($region == 'Utena') echo 'selected'; ?>>Utena</option>
			<option value="Tauragė" <?php if($region == 'Tauragė') echo 'selected'; ?>>Tauragė</option>
            <option value="Telšiai" <?php if($region == 'Telšiai') echo 'selected'; ?>>Telšiai</option>
		</select>
        <button type="submit">Rodyti grafiką</button>
    </form>
	<?php if (isset($chartFile)): ?>
        <img src="wind_rose.png">
    <?php endif; ?>
</body>
</html>