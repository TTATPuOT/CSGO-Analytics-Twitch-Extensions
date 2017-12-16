<?php
header('Access-Control-Allow-Origin: *');
/*
if (!empty($_POST['steamid'])) {
	include_once("../../config.php");
	include_once("check_steamid.php");

	$steamid = checkSteamID($_POST['steamid'], $valve_key);

	if ($steamid != false) {
		$json_live = json_decode(strip_tags(file_get_contents("http://convars.com/api/score.php?steamid=".$steamid)), true);

		if (!empty($json_live) AND $json_live['status'] == "live") {
			if ('score'.$json_live['team'] == "scoreA") {
				$user_score = $json_live['scoreA'];
				$enemy_score = $json_live['scoreB'];
			} else{
				$user_score = $json_live['scoreB'];
				$enemy_score = $json_live['scoreA'];
			}
			$result = array(
				'live' => 1,
				'data' => array(
					'map' => $json_live['map'],
					'user_score' => $user_score,
					'enemy_score' => $enemy_score,
					'time' => $json_live['time']." minutes",
					'bg' => $maps_bg[$json_live['map']]
					)
				);
		} else{
			$result = array(
				'live' => 0
				);
		}

		if ($steamid == "76561198021408948") {
			$result = array(
				'live' => 1,
				'data' => array(
					'map' => "de_dust2",
					'user_score' => "12",
					'enemy_score' => "10",
					'time' => "27 minutes",
					'bg' => $maps_bg['de_dust2']
					)
				);
		}
		$result = array(
			'live' => 0
			);

		echo json_encode($result);
	}
}
*/
$result = array(
	'live' => 0
	);

echo json_encode($result);

?>