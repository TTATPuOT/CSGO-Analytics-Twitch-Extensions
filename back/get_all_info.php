<?php
header('Access-Control-Allow-Origin: *');
if (!empty($_POST['steamid'])) {
	include_once("../../config.php");
	include_once("check_steamid.php");

	$steamid = checkSteamID($_POST['steamid'], $valve_key);

	if ($steamid != false) {

		$nick = getNick($steamid, $valve_key);
		$avatar = getAvatar($steamid, $valve_key);
		$result['nick'] = $nick;
		$result['avatar'] = $avatar;

		$json_stats = file_get_contents("http://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key=".$valve_key."&steamid=".$steamid);
		$json_stats = json_decode(html_entity_decode($json_stats), true);
		$json_stats = $json_stats['playerstats']['stats'];

		$maps = array();
		$maps_big = array();
		$weapons = array();

		foreach ($json_stats as $value) {
			if ($value['name'] == "total_kills") {
				$result['kills'] = $value['value'];
			} elseif ($value['name'] == "total_deaths"){
				$result['deaths'] = number_format($value['value']);
			} elseif ($value['name'] == "total_time_played"){
				$result['time'] = number_format($value['value']);
			} elseif ($value['name'] == "total_planted_bombs"){
				$result['planted'] = number_format($value['value']);
			} elseif ($value['name'] == "total_defused_bombs"){
				$result['defused'] = number_format($value['value']);
			} elseif ($value['name'] == "total_wins"){
				$result['wins'] = number_format($value['value']);
			} elseif ($value['name'] == "total_damage_done"){
				$result['dmg'] = number_format($value['value']);
			} elseif ($value['name'] == "total_kills_headshot"){
				$result['hs'] = $value['value'];
			} elseif (preg_match("/total_rounds_map_/", $value['name'])){
				$map = str_replace("total_rounds_map_", "", $value['name']);
				$maps[$map] = $value['value'];
			} elseif (preg_match("/total_kills_/", $value['name'])){

				if ($value['name'] != "total_kills_enemy_weapon" AND $value['name'] != "total_kills_headshot" AND $value['name'] != "total_kills_enemy_blinded" AND $value['name'] != "total_kills_knife_fight" AND $value['name'] != "total_kills_against_zoomed_sniper" AND $value['name'] != "total_kills_against_zoomed_sniper") {

					$weapon = str_replace("total_kills_", "", $value['name']);
					$weapons[$weapon] = $value['value'];
				}
			}
		}
		arsort($maps);
		$result['map'] = key($maps);

		$result['hs'] = round($result['hs']/($result['kills']/100), 2);

		$result['kills'] = number_format($result['kills']);

		arsort($weapons);
		$i = 0;
		$result['weapons'] = array();
		foreach ($weapons as $weapon => $kills) {
			if ($i < 3) {
				$result['weapons'][] = array(
					'name' => $weapon,
					'kills' => $kills
					);
			} else{
				break;
			}
			$i += 1;
		}

		echo json_encode($result);
	} else{
		die(json_encode(array('error' => 1)));
	}
}

?>