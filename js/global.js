/*
 * MunroeScore.com
 */

$(function() {
	// Handle external links
	$('a[rel="external"]').click(function() {
		var href = $(this).attr('href');

		// Send external link event to Google Analaytics
		try {
			_gaq.push(['_trackEvent','External Links', href.split(/\/+/g)[1], href]);
		} catch (e) {};

		window.open(href,'ms_'+Math.round(Math.random()*11));
		return false;
	});
});
