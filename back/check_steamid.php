<?php

$maps_bg = array(
	"de_dust2" => 1,
	"de_cbble" => 2,
	"de_inferno" => 3,
	"de_nuke" => 4,
	"de_train" => 5,
	"cs_office" => 6,
	"de_lake" => 7,
	"cs_italy" => 8,
	"de_dust" => 9,
	"cs_assault" => 10,
	"de_aztec" => 11,
	"de_bank" => 12,
	"de_safehouse" => 13,
	"de_stmarc" => 14,
	"cs_militia" => 15,
	"de_vertigo" => 16,
	"ar_baggage" => 17,
	"ar_shoots" => 18,
	"ar_monastery" => 19,
	"de_sugarcane" => 20,
	"de_mirage" => 21,
	"" => 22
	);

function checkSteamID($steamid, $valve_key){

	//$true_steamid = false;

	if (is_numeric($steamid)) {
		$json_steamid = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$valve_key."&steamids=".$steamid), TRUE)['response']['players'][0]['steamid'];
		if (!empty($json_steamid)) {
			$true_steamid = $json_steamid;
		}
	}


	if (empty($true_steamid)) {
		$json_steamid = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key=".$valve_key."&vanityurl=".$steamid), TRUE)['response']['steamid'];
		if (!empty($json_steamid)) {
			$true_steamid = $json_steamid;
		}
	}

	if (empty($true_steamid)) {
		$steamid_array = explode("/", $steamid);
		if (end($steamid_array) == "/" OR end($steamid_array) == "") {
			$steamid = $steamid_array[count($steamid_array)-2];
		} else{
			$steamid = end($steamid_array);
		}
		$json_steamid = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key=".$valve_key."&vanityurl=".$steamid), TRUE)['response']['steamid'];
		if (!empty($json_steamid)) {
			$true_steamid = $json_steamid;
		}
	}

	if (!empty($true_steamid)) {
		return $true_steamid;
	} else{
		return false;
	}
}

function getNick($steamid, $valve_key){
	$nick = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$valve_key."&steamids=".$steamid), TRUE)['response']['players'][0]['personaname'];
	if (!empty($nick)) {
		return $nick;
	} else{
		return false;
	}
}

function getAvatar($steamid, $valve_key){
	$avatar = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$valve_key."&steamids=".$steamid), TRUE)['response']['players'][0]['avatarfull'];
	if (!empty($avatar)) {
		return $avatar;
	} else{
		return false;
	}
}

?>