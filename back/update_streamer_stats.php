<?php

function updateStreamerStats($steamid, $valve_key, $maps_bg, $mysqli, $return_json = false){

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
	$weapons_big = array();

	foreach ($json_stats as $value) {
		if ($value['name'] == "total_kills") {
			$result['kills'] = $value['value'];
		} elseif ($value['name'] == "total_deaths"){
			$result['deaths'] = $value['value'];
		} elseif ($value['name'] == "total_mvps"){
			$result['mvps'] = $value['value'];
		} elseif ($value['name'] == "total_time_played"){
			$result['time'] = $value['value'];
		} elseif ($value['name'] == "total_planted_bombs"){
			$result['planted'] = $value['value'];
		} elseif ($value['name'] == "total_defused_bombs"){
			$result['defused'] = $value['value'];
		} elseif ($value['name'] == "total_wins"){
			$result['wins'] = $value['value'];
		} elseif ($value['name'] == "total_damage_done"){
			$result['dmg'] = $value['value'];
		} elseif ($value['name'] == "total_kills_headshot"){
			$result['hs'] = $value['value'];
			$result['hs_count'] = $value['value'];
		} elseif (preg_match("/total_rounds_map_/", $value['name'])){
			$map = str_replace("total_rounds_map_", "", $value['name']);
			$maps[$map] = $value['value'];
			$maps_big[$map]['total_rounds_map'] = $value['value'];
		} elseif (preg_match("/total_kills_/", $value['name'])){

			if ($value['name'] != "total_kills_enemy_weapon" AND $value['name'] != "total_kills_headshot" AND $value['name'] != "total_kills_enemy_blinded" AND $value['name'] != "total_kills_knife_fight" AND $value['name'] != "total_kills_against_zoomed_sniper" AND $value['name'] != "total_kills_against_zoomed_sniper") {

				$weapon = str_replace("total_kills_", "", $value['name']);
				$weapons[$weapon] = $value['value'];
			}
		} elseif(preg_match("/total_shots_/", $value['name'])){
			$weapon = str_replace("total_shots_", "", $value['name']);
			$weapons_big[$weapon]['total_shots'] = $value['value'];
		} elseif(preg_match("/total_hits_/", $value['name'])){
			$weapon = str_replace("total_hits_", "", $value['name']);
			$weapons_big[$weapon]['total_hits'] = $value['value'];
		} elseif (preg_match("/total_wins_map_/", $value['name'])) {
			$map = str_replace("total_wins_map_", "", $value['name']);
			$maps_big[$map]['total_wins_map'] = $value['value'];
		} elseif ($value['name'] == "total_rounds_played") {
			$result['rounds_played'] = $value['value'];
		} elseif ($value['name'] == "total_matches_won") {
			$result['matches_won'] = $value['value'];
		} elseif ($value['name'] == "total_matches_played") {
			$result['matches_played'] = $value['value'];
		}
	}

	foreach ($maps_big as $key => $map) {
		$total_rounds_map = (!empty($map['total_rounds_map'])) ? $map['total_rounds_map'] : 0;
		$total_wins_map = (!empty($map['total_wins_map'])) ? $map['total_wins_map'] : 0;
		$map_winrate = round($total_wins_map/($total_rounds_map/100), 2);

		if (!empty($maps_bg[$key])) {
			$bg_id = $maps_bg[$key];
		} else{
			$bg_id = 0;
		}

		unset($maps_big[$key]);
		$maps_big[] = array(
			'total_rounds_map_sort' => $total_rounds_map,
			'total_rounds_map' => $total_rounds_map,
			'total_wins_map' => $total_wins_map,
			'winrate' => $map_winrate,
			'name' => $key,
			'bg_id' => $bg_id,
			);
	}
	rsort($maps_big);
	foreach ($maps_big as $key => $map) {
		unset($maps_big[$key]['total_rounds_map_sort']);
	}

	foreach ($weapons_big as $key => $weapon) {
		$total_shots = (!empty($weapon['total_shots'])) ? $weapon['total_shots'] : 0;
		$total_hits = (!empty($weapon['total_hits'])) ? $weapon['total_hits'] : 0;
		$accuracy = round($total_hits/($total_shots/100), 2);

		unset($weapons_big[$key]);

		if ($key != "knife" AND $key != "hegrenade" AND $key != "hit" AND $key != "fired" AND $key != "molotov" AND $key != "decoy" AND $key != "taser") {
			$weapons_big[$key] = array(
				'total_shots' => $total_shots,
				'total_hits' => $total_hits,
				'accuracy' => $accuracy
				);
		}
	}

	arsort($maps);

	$result['shots'] = 0;
	$result['hits'] = 0;

	foreach ($weapons_big as $weapon) {
		$result['shots'] += $weapon['total_shots'];
		$result['hits'] += $weapon['total_hits'];
	}

	$result['accuracy'] = round($result['hits']/($result['shots']/100), 2);

	$result['hs'] = round($result['hs']/($result['kills']/100), 2);

	$result['kd'] = round($result['kills']/$result['deaths'], 2);

	$result['winrate'] = round($result['wins']/($result['rounds_played']/100), 2);

	$result['matches_winrate'] = round($result['matches_won']/($result['matches_played']/100), 2);

	$json_achievements = json_decode(html_entity_decode(file_get_contents("https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?format=json&appid=730&key=".$valve_key."&steamid=".$steamid)), true)['playerstats']['achievements'];

	$achievements_unlock = 0;
	$achievements = 0;

	foreach ($json_achievements as $achive) {
		if ($achive['achieved'] == 1) {
			$achievements_unlock += 1;
		}
		$achievements += 1;
	}

	$result['achievements'] = $achievements_unlock."/".$achievements;

	$result['map'] = key($maps);
	$result['maps_big'] = $maps_big;

	arsort($weapons);
	$i = 0;
	$result['weapons'] = array();
	foreach ($weapons as $weapon => $kills) {
		if ($i < 4) {
			$result['weapons'][] = array(
				'name' => $weapon,
				'kills' => $kills,
				'shots' => $weapons_big[$weapon]['total_shots'],
				'hits' => $weapons_big[$weapon]['total_hits'],
				'accuracy' => $weapons_big[$weapon]['accuracy']
				);
		} else{
			break;
		}
		$i += 1;
	}

	$result['kills'] = number_format($result['kills']);
	$result['deaths'] = number_format($result['deaths']);
	$result['time'] = number_format($result['time']);
	$result['planted'] = number_format($result['planted']);
	$result['defused'] = number_format($result['defused']);
	$result['wins'] = number_format($result['wins']);
	$result['dmg'] = number_format($result['dmg']);
	$result['hs_count'] = number_format($result['hs_count']);
	$result['rounds_played'] = number_format($result['rounds_played']);
	$result['mvps'] = number_format($result['mvps']);
	$result['matches_won'] = number_format($result['matches_won']);
	$result['matches_played'] = number_format($result['matches_played']);
	$result['shots'] = number_format($result['shots']);
	$result['hits'] = number_format($result['hits']);

	$result['hs'] = $result['hs']."%";
	$result['accuracy'] = $result['accuracy']."%";
	$result['winrate'] = $result['winrate']."%";
	$result['matches_winrate'] = $result['matches_winrate']."%";

	foreach ($result['maps_big'] as $key => $map) {
		$result['maps_big'][$key]['total_rounds_map'] = number_format($map['total_rounds_map']);
		$result['maps_big'][$key]['total_wins_map'] = number_format($map['total_wins_map']);
		$result['maps_big'][$key]['winrate'] = $map['winrate']."%";
	}
	foreach ($result['weapons'] as $key => $weapon) {
		$result['weapons'][$key]['kills_num'] = $weapon['kills'];
		$result['weapons'][$key]['kills'] = number_format($weapon['kills']);
		$result['weapons'][$key]['shots'] = number_format($weapon['shots']);
		$result['weapons'][$key]['hits'] = number_format($weapon['hits']);
		$result['weapons'][$key]['accuracy'] = $weapon['accuracy']."%";
	}

	$result_json = mysqli_escape_string($mysqli, json_encode($result));

	$mysqli->query("UPDATE twitch SET stats='$result_json' WHERE steamid='$steamid'");

	if ($return_json) {
		return json_encode($result);
	}
}

?>