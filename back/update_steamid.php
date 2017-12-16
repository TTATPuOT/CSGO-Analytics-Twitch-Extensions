<?php

header('Access-Control-Allow-Origin: *');

if (!empty($_POST['steamid']) AND !empty($_POST['twitch_id'])){

	include_once("../../config.php");
	include_once("check_steamid.php");
	
	$steamid = $_POST['steamid'];
	$twitch_id = mysqli_escape_string($mysqli, $_POST['twitch_id']);

	$steamid = checkSteamID($steamid, $valve_key);

	if ($steamid == false) {
		die(json_encode(array('error' => 'This SteamID is not exist.')));
	}

	$repeat = mysqli_fetch_array($mysqli->query("SELECT id FROM twitch WHERE twitch_id = '$twitch_id' LIMIT 1"))['id'];

	if (!empty($repeat) AND $repeat != 0) {

		$date = date("Y-m-d H:i:s");

		if ($mysqli->query("UPDATE twitch SET steamid='$steamid', updated='$date' WHERE twitch_id='$twitch_id' LIMIT 1")) {
			die(json_encode(array('result' => 1)));
		} else{
			die(json_encode(array('error' => 'Error on updating you in database. Try again.')));
		}

	} else{
		$mysqli -> query("INSERT INTO users (steamid) VALUES ('$steamid')");
		if ($mysqli->query("INSERT INTO twitch(twitch_id, steamid) VALUES ('$twitch_id', '$steamid')")) {
			die(json_encode(array('result' => 1)));
		} else{
			die(json_encode(array('error' => 'Error on adding you in database. Try again.')));
		}
	}

} else{
	die("Auth error! Get out here!");
}

?>