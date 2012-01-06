/*
 * MunroeScore.com
 */

// On page load
$(function() {
	$('#getForm').submit(startSearch);
});

var APIURL = 'http://en.wikipedia.org/w/api.php';
var startSearch = function() {
	var page = $('#search').val();

	loadPage(page);
};

// Sends JSON request to specified page
var loadPage = function(page) {
	$.getJSON(APIURL,{
		titles: page,
		action: 'query',
		format: 'json',
		prop: 'revsions',
		rvprop: 'content'
	}, procJSON);
};

// Process JSON data from wikipedia API
var procJSON = function(data) {
	var page = data.query.pages[0];
	var title = page.title;

	// If no revisions, page was not found
	if (page.revisions == null) {
		pageNotFound(page);
		return;
	}

	var content = page.revisions[0]['*'];

	findNextPage(content);
};

// Parses content for the next proper page
var findNextPage = function() {

});
