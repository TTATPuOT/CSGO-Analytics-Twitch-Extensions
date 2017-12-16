var twitch_id = null;
window.Twitch.ext.onAuthorized(function(auth) {
	twitch_id = auth.channelId;
	$.ajax({
		url: 'https://csgo-analytics.ru/twitch_extension/back/get_steamid.php',
		type: 'POST',
		dataType: 'json',
		data: {twitch_id: twitch_id}
	})
	.always(function(data) {
		$("#battletag").val(data.result);
	});
});
$("form").submit(function(event) {
	event.preventDefault();
	$("button.send").text("Wait...");
	$(".result").empty();

	var steamid = $("#battletag").val();
	if (twitch_id != null) {
		if (steamid != "") {
			$.ajax({
				url: 'https://csgo-analytics.ru/twitch_extension/back/update_steamid.php',
				type: 'POST',
				dataType: 'json',
				data: {steamid: steamid, twitch_id: twitch_id}
			})
			.always(function(data) {
				if (data.result == 1) {
					$(".result").text("All good! Now you can start streaming.");
					$("input").remove();
					$("button").remove();
				} else{
					$(".result").text(data.error);
				};
				$("button.send").text("Update");
			});
		} else{
			$("button.send").text("Update");
			$(".result").text("Please, fill the input.")
		};
	} else{
		$("button.send").text("Update");
		$(".result").text("Your are not authed. Try again")
		console.log("You are not authed, sorry :(");
	};
});