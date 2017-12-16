<?php

header('Access-Control-Allow-Origin: *');
if (!empty($_POST['steamid'])) {
	include_once("../../config.php");
	//include_once("check_steamid.php");


	$steamid = mysqli_escape_string($mysqli, $_POST['steamid']);

	$twitch_id = (!empty($_POST['twitch_id'])) ? mysqli_escape_string($mysqli, $_POST['twitch_id']) : 0 ;


	if (!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = mysqli_escape_string($mysqli, $_SERVER['HTTP_CLIENT_IP']);
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = mysqli_escape_string($mysqli, $_SERVER['HTTP_X_FORWARDED_FOR']);
	} else{
		$ip = mysqli_escape_string($mysqli, $_SERVER['REMOTE_ADDR']);
	}
	$mysqli->query("INSERT INTO twitch_sessions(twitch_id, steam_id, ip) VALUES ('$twitch_id', '$steamid', '$ip')");

	$stats_json = mysqli_fetch_array($mysqli->query("SELECT stats FROM twitch WHERE steamid='$steamid' LIMIT 1"))['stats'];

	if (!empty($stats_json)) {
		echo $stats_json;
		die();
	} else{
		include_once("update_streamer_stats.php");
		echo updateStreamerStats($steamid, $valve_key, $maps_bg, $mysqli);
		die();
	}
}

?>