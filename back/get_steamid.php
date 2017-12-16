<?php

header('Access-Control-Allow-Origin: *');

if (!empty($_POST['twitch_id'])){

	include_once("../../config.php");

	$twitch_id = mysqli_escape_string($mysqli, $_POST['twitch_id']);

	$steamid = mysqli_fetch_array($mysqli->query("SELECT steamid FROM twitch WHERE twitch_id = '$twitch_id' LIMIT 1"))['steamid'];

	if (!empty($steamid)) {
		die(json_encode(array('result' => $steamid), true));
	} else{
		die(json_encode(array('result' => ''), true));
	}

} else{
	die("Auth error! Get out here!");
}

?>