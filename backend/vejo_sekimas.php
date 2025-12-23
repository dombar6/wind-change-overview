<?php
session_start();
include_once 'db.php';
$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8mb4");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';


use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load()

require '../vendor/autoload.php';

$query = "SELECT v.id, v.epastas, p.regionas, p.vejo_stiprumas_nuo, p.vejo_stiprumas_iki, p.vejo_kryptis 
          FROM vartotojai v
          JOIN pranesimai p ON v.id = p.vartotojo_id";
$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->get_result();

while ($row = $results->fetch_assoc()) {
    $user_email = $row['epastas'];
    $region = $row['regionas'];
	
	$api_key = $_ENV['WEATHER_API_KEY'];
    $url = "http://api.openweathermap.org/data/2.5/weather?q=$region,LT&appid=$api_key&units=metric";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
	
	if ($data && isset($data['wind'])) {
		$current_wind_speed = $data['wind']['speed'];
		$current_wind_deg = $data['wind']['deg'];
		$directions = ["Šiaurė", "Šiaurės Rytai", "Rytai", "Pietryčiai", "Pietūs", "Pietvakariai", "Vakarai", "Šiaurės Vakarai"];
		$compass_direction = $directions[round($current_wind_deg / 45) % 8];

		$wind_from = $row['vejo_stiprumas_nuo'];
		$wind_to = $row['vejo_stiprumas_iki'];
		$user_wind_direction = $row['vejo_kryptis'];

		$is_speed_valid = null;
		if ($wind_from !== null && $wind_from !== '' && $current_wind_speed >= $wind_from && $current_wind_speed <= $wind_to) {
			$is_speed_valid = true;
		} else{
			$is_speed_valid = false;
		}

		$is_direction_valid = null;
		if ($user_wind_direction !== null && $user_wind_direction !== '' && $compass_direction === $user_wind_direction) {
			$is_direction_valid = true;
		} else{
			$is_direction_valid = false;
		}
		
		if ($is_speed_valid && $is_direction_valid) {
			$mail = new PHPMailer(true);
			try {
				$mail->isSMTP();
				$mail->Host = 'smtp.sendgrid.net';
				$mail->SMTPAuth = true;
				$mail->Username = 'apikey';
				$mail->Password = $_ENV['SEND_GRID_API_KEY'];
				$mail->SMTPSecure = 'tls';
				$mail->Port = 587;
				$mail->setFrom('dbartkievicius@gmail.com', 'Vėjo stebėjimo sistema');
				$mail->addAddress($user_email);

				$mail->isHTML(true);
				$mail->Subject = 'Vėjo pokyčiai jūsų pasirinktoje vietovėje';
				$mail->Body = "Vėjo greitis: $current_wind_speed m/s, kryptis: $compass_direction.<br>Jūsų pasirinktas regionas: $region.";

				$mail->send();
				echo "Pranešimas išsiųstas į $user_email.\n";
			} catch (Exception $e) {
				echo "Nepavyko išsiųsti el. laiško vartotojui $user_email: {$mail->ErrorInfo}\n";
			}
		}
	}
}
?>