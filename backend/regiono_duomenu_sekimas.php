<?php
include_once 'db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

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

$api_key = 'ac1ae9af2c2715ba8a2df1fa5171c867';

foreach ($regions as $region) {
    $url = "http://api.openweathermap.org/data/2.5/weather?q=$region,LT&appid=$api_key&units=metric";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
	
	if ($data && isset($data['wind'])) {
        $current_wind_speed = $data['wind']['speed'];
        $current_wind_deg = $data['wind']['deg'];
		
		$directions = ["Šiaurė", "Šiaurės Rytai", "Rytai", "Pietryčiai", "Pietūs", "Pietvakariai", "Vakarai", "Šiaurės Vakarai"];
        $compass_direction = $directions[round($current_wind_deg / 45) % 8];
		
		$stmt = $conn->prepare("INSERT INTO regiono_duomenys (regionas, vejo_greitis, vejo_kryptis) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $region, $current_wind_speed, $compass_direction);
        $stmt->execute();
        $stmt->close();
	}
}
?>
