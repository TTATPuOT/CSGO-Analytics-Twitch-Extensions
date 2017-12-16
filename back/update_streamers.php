<?php

include_once("/home/n/neverov12/csgo-analytics.ru/public_html/config.php");
include_once("/home/n/neverov12/csgo-analytics.ru/public_html/twitch_extension/back/check_steamid.php");
include_once("/home/n/neverov12/csgo-analytics.ru/public_html/twitch_extension/back/update_streamer_stats.php");

$current_user = intval(mysqli_fetch_array($mysqli->query("SELECT view_count FROM twitch WHERE id='0' LIMIT 1"))['view_count']);

$streamers = $mysqli->query("SELECT id, twitch_id, steamid FROM twitch WHERE id > '$current_user' LIMIT 10");

if (mysqli_num_rows($streamers) == 0) {
	$current_user = 0;
	$mysqli->query("UPDATE twitch SET view_count='$current_user' WHERE id='0' LIMIT 1");
	$streamers = $mysqli->query("SELECT id, twitch_id, steamid FROM twitch WHERE id > '$current_user' LIMIT 10");
}

while ($row = mysqli_fetch_array($streamers)) {

	$id = $row['id'];
	$twitch_id = $row['twitch_id'];

	#####
	$steamid = mysqli_escape_string($mysqli, $row['steamid']);
	updateStreamerStats($steamid, $valve_key, $maps_bg, $mysqli);
	if (!empty($steamid)) {
		$repeat = mysqli_fetch_array($mysqli->query("SELECT id FROM users WHERE steamid = '$steamid' LIMIT 1"))['id'];
		if (empty($repeat)) {
			$mysqli -> query("INSERT INTO users (steamid) VALUES ('$steamid')");
		}
	}
	#####

	$request = array(
		"id" => $twitch_id
		);
	$url = 'https://api.twitch.tv/helix/users';

	$request = http_build_query($request);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url."?".$request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Client-ID: zyt20fl337b4xbaisl72m23j5ipkce'));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

	$response = json_decode(curl_exec($ch), true)['data'][0];
	curl_close($ch);

	$login = mysqli_escape_string($mysqli, $response['login']);
	$view_count = mysqli_escape_string($mysqli, $response['view_count']);
	$broadcaster_type = mysqli_escape_string($mysqli, $response['broadcaster_type']);
	$type = mysqli_escape_string($mysqli, $response['type']);
	$nick = mysqli_escape_string($mysqli, $response['display_name']);

	sleep(1);

	$request = array(
		"user_id" => $twitch_id
		);
	$url = 'https://api.twitch.tv/helix/streams';

	$request = http_build_query($request);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url."?".$request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Client-ID: zyt20fl337b4xbaisl72m23j5ipkce'));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

	$response = json_decode(curl_exec($ch), true)['data'][0];

	curl_close($ch);
	if (!empty($response['type'])) {
		$live = mysqli_escape_string($mysqli, $response['type']);
	} else{
		$live = "offline";
	}


	if (!empty($login)) {
		if ($mysqli->query("UPDATE twitch SET login='$login', nick='$nick', view_count='$view_count', broadcaster_type='$broadcaster_type', type='$type', live='$live' WHERE id='$id' LIMIT 1")){
			$current_user = $id;
		}
	}

	sleep(1);

}

$mysqli->query("UPDATE twitch SET view_count='$current_user' WHERE id='0' LIMIT 1");

?>