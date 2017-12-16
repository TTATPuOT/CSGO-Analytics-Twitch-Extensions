var steamid = null;
var twitch_id = null;
var intervalLiveUpdater = null;
var panel_toggler = true;

var getUrlParameter = function getUrlParameter(sParam) {
	var sPageURL = decodeURIComponent(window.location.search.substring(1)),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');

		if (sParameterName[0] === sParam) {
			return sParameterName[1] === undefined ? true : sParameterName[1];
		}
	}
};

function show_block(element, delay, display = 'block', type = 'zoomIn'){
	element.css('display', display);
	element.addClass(type+' delay_'+delay);
}
function hide_block(element){
	element.css('display', 'none');
}

function animateBigData(){
	var timer = 0;
	$(".big_data .column").each(function(index, el) {
		show_block($(this), timer);
		timer += 2;
	});
}
function animatePanel(){
	var timer = 0;
	$(".panel .left").each(function(index, el) {
		show_block($(this), timer);
	});
	timer = 2;
	$(".panel .information").each(function(index, el) {
		show_block($(this), timer);
	});
}

function goToBigData(){
	$(".panel").removeClass('active');
	$(".big_data").addClass('active');
	animateBigData();
}

function updateLiveMode(){
	if (steamid != "" && steamid != null) {
		$.ajax({
			url: 'https://csgo-analytics.ru/twitch_extension/back/get_user_live.php',
			type: 'POST',
			dataType: 'json',
			data: {steamid: steamid, anchor: getUrlParameter('anchor')},
		})
		.done(function(data) {
			if (data.live == 1) {
				var team = data.data.team;
				$(".label .live").addClass('active map_'+data.data.bg);
				$(".panel_fix.live").addClass('active map_'+data.data.bg);
				$(".live_score span:first-of-type").text(data.data.user_score);
				$(".live_score span:last-of-type").text(data.data.enemy_score);
				$(".live_time").text(data.data.time);
				$(".live_map").text(data.data.map);
			} else{
				$(".label .live").removeClass('active');
				$(".panel_fix.live").removeClass('active');
			};
		})
		.always(function(data) {
			console.log(data);
		});
	}
}

function updateStats(steamid) {
	if (steamid != "" && steamid != null) {
		$.ajax({
			url: 'https://csgo-analytics.ru/twitch_extension/back/get_all_info2.php',
			type: 'POST',
			dataType: 'json',
			data: {steamid: steamid, twitch_id: twitch_id},
		})
		.done(function(data) {
			console.log(data);
			if (data.error != "") {

				//Main info
				$(".panel .avatar .image").css('background-image', 'url("'+data.avatar+'")');
				$(".label .avatar").css('background-image', 'url("'+data.avatar+'")');
				$(".nick").text(data.nick);

				$(".kills").text(data.kills);
				$(".deaths").text(data.deaths);
				$(".kd").text(data.kd);
				$(".shots").text(data.shots);
				$(".hits").text(data.hits);
				$(".accuracy").text(data.accuracy);
				$(".time").text(data.time);
				$(".mvps").text(data.mvps);
				$(".bombs").text(data.planted+"/"+data.defused);
				$(".wins").text(data.wins);
				$(".dmg").text(data.dmg);
				$(".winrate").text(data.winrate);
				$(".matches_played").text(data.matches_played);
				$(".matches_won").text(data.matches_won);
				$(".headshots").text(data.hs_count);
				$(".headshots_p").text(data.hs);
				$(".achievements").text(data.achievements);
				$(".comp_table .map").text(data.map);
				var full_time = data.weapons[0].kills_num/100;
				$(".timebars-comp .bar").each(function(index, el) {
					$(this).find('.hero').empty();
					$(this).find('.hero').append("<b>"+data.weapons[index].name+"</b> - "+data.weapons[index].kills+" kills");
					$(this).find('.progress-bar').css('width', (data.weapons[index].kills_num/full_time)+"%");
				});

				$(".big_data table.map").each(function(index, el) {
					$(this).addClass('map_'+data.maps_big[index].bg_id);
					$(this).find("span.map_name").text(data.maps_big[index].name);
					$(this).find("span.map_wins b").text(data.maps_big[index].total_wins_map);
					$(this).find(".rounds_plays").text(data.maps_big[index].total_rounds_map);
					$(this).find(".rounds_win").text(data.maps_big[index].total_wins_map);
					$(this).find(".map_winrate").text(data.maps_big[index].winrate);
				});

				$(".big_data table.weapon").each(function(index, el) {
					$(this).find("span.weapon_name").text(data.weapons[index].name);
					$(this).find("span.weapon_kills b").text(data.weapons[index].kills);
					$(this).find(".weapon_shots").text(data.weapons[index].shots);
					$(this).find(".weapon_acc").text(data.weapons[index].accuracy);
				});

				$(".panel:not(.viewer_block)").addClass('active');

				$(".screen_loading").fadeOut('500', function() {});
			};
		})
		.fail(function() {
			updateStats(steamid);
		})
		.always(function(data) {
		});
	} else{
		$("body").css('display', 'none');
	};
}
jQuery(document).ready(function($) {

	$(".go_to_more_button").click(function(event) {
		goToBigData();
	});

	$(".close_button").click(function(event) {
		$(".panel").removeClass('active');
		$(".label.logo").removeClass('active');
		$(".big_data").removeClass('active');
		panel_toggler = true;
	});

	$(".label.logo").click(function(event) {

		if (panel_toggler) {

			$(".panel").addClass('active');
			$(".label.logo").addClass('active');
			panel_toggler = false;
		} else{

			$(".panel").removeClass('active');
			$(".label.logo").removeClass('active');
			$(".big_data").removeClass('active');
			panel_toggler = true;
		};
		animatePanel();
	});

	$(".go_to_more_button_simple").click(function(event) {

		document.body.scrollTop = document.documentElement.scrollTop = 0;
		$(".panel").removeClass('active');
		$(".label").removeClass('active');
		hide_block($(".label"));
		$(".panel_fix.live").css('display', 'none');
		goToBigData();
	});

	$(".go_to_less_button_simple").click(function(event) {

		document.body.scrollTop = document.documentElement.scrollTop = 0;
		$(".panel").addClass('active');
		animatePanel();
		$(".label").addClass('active');
		show_block($(".label"), 0, 'block', 'slideInLeft');
		$(".big_data").removeClass('active');
		$(".panel_fix.live").attr('style', '');
	});
});
window.Twitch.ext.onAuthorized(function(auth) {
	twitch_id = auth.channelId;
	$.ajax({
		url: 'https://csgo-analytics.ru/twitch_extension/back/get_steamid.php',
		type: 'POST',
		dataType: 'json',
		data: {twitch_id: twitch_id}
	})
	.always(function(data) {
		steamid = data.result;
		updateStats(steamid);
		updateLiveMode();
		intervalLiveUpdater = setInterval(updateLiveMode, 60000);
	});
	console.log(twitch_id);
});