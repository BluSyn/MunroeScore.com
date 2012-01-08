/*
 * MunroeScore.com
 */

// On page load
$(function() {
	$('#getForm').submit(startSearch);
});

// Tracks load times
var _parsetime = 0;
var _searchtime = 0;

// Debug function, to be removed later
var debug = function(data) {
	console.log(data);
};

var APIURL = 'http://en.wikipedia.org/w/api.php?callback=?';
var startSearch = function() {
	var page = $('#search').val();

	debug('Starting Search: '+page);

	_searchtime = (new Date()).getMilliseconds();
	loadPage(page.replace(' ','_'), 1);

	// Prevent form from submitting
	return false;
};

// Sends JSON request to specified page
var loadPage = function(page, score) {
	$.getJSON(APIURL,{
		page: decodeURIComponent(page),
		action: 'parse',
		format: 'json',
		prop: 'text',
		uselang: 'en'
	}, function (data) { return parseWiki(data, score); });
};

// Ensures we need to continue loading the next page
var goNextPage = function(page, score) {
	// Send proccessing time to debug
	var _endtime = (new Date()).getMilliseconds();
	debug('Process Time: '+(_endtime-_parsetime)+'ms');

	// Make sure a proper value was received
	if (page == '' || page == null) {
		pageNotFound(page);
		return;
	}

	// Check if this page matches our 'final' page settings
	if (page === 'Philosophy') {
		finalPageFound(page, score);
		return;
	}

	console.log('Page #'+score+': '+page);

	// Continue loading the next page
	return loadPage(page, score+1);
};

var pageNotFound = function(page) {
	debug(page);
};

var finalPageFound = function(page, score) {
	debug(page);
	debug('Success! Philosophy found');
	debug('Munroe Score: '+score);

	var _endtime = (new Date()).getMilliseconds();
	debug('Time To Find: '+(_endtime-_searchtime)+'ms');
};

// Parse out the important information from wiki JSON response
var parseWiki = function(data, score) {
	// Track parse time
	_parsetime = (new Date()).getMilliseconds();

	if (!data || !data.parse || !data.parse.text) {
		debug('Error: Could not load page');
		return;
	}

	var content = data.parse.text['*'] || '';

	// Remove all src="" values from content
	// this prevents external resources (images) from loading
	// when the content is injected into the DOM
	var content = content.replace(/src="[0-9A-Z_%\.\-\(\)\\\/]+"/ig,'');

	// Replace all href="/wiki/*" values to convert parenthesis
	// to their URL equivalent. This is important for when all ()
	// are removed from the content in the next step
	var ma = content.match(/href="\/wiki\/[0-9A-Z_%\-\(\)]+"/ig);
	for (var match in ma) {
		var esc = ma[match].replace('(', '%28').replace(')','%29');
		content = content.replace(ma[match], esc);
	}

	// Remove all parantheses and bracket sections
	// from content to stay within the confines of Munroe's Law
	// Do it as many times as needed to deal with "nested" groups
	do {
		content = content.replace(/\([^\)\(]+\)/, '');
		//content = content.replace(/\[[^\]\[]+\]/g, '');
	} while(content.indexOf(')') !== -1);

	// Parse content in jQuery
	var html = $('<div id="parent">'+content+'</div>');

	// Check for redirection
	var redirect = html.find('li:contains("REDIRECT") a').text();
	if (redirect != '') {
		// Send page name back to loadPage
		loadPage(redirect.replace('/wiki/',''));
		return;
	}

	// Remove tables from HTML to save time skipping them
	html.remove('table');

	// Find the link
	return goNextPage(findLink(html), score);
};


/*
 * Looks for first link that meets the following conditions:
 * - Part of the main article setion
 * - Not "italicized"
 * - Not a "File:" or "Wikipedia:" article
 * - Not a anchor tag (starts with #)
 */
var findLink = function(html, pnum) {
	if (!html) return false;

	// Start with the first paragraph
	var pnum = typeof(pnum) != 'undefined' ? pnum : 0;
	var para = html.children('p:eq('+pnum+')');

	// Increment pnum now so it
	// is passed easily for next paragraph
	pnum += 1;

	// Verify parents: Exclude div.dablink
	if (para.parents('div.dablink').length || para.parents('table').length) {
		return findLink(html, pnum);
	}

	// Grab all links that are not inside italics
	var links = para.find('a:not(i a)');
	var numlinks = links.length;

	// Verify proper links
	var badLink = false;
	var lnum = 0;
	do {
		// If we go past the total number of links,
		// then skip to next paragraph
		if (lnum >= numlinks) {
			return findLink(html, pnum);
		}

		// Get current link object for verification
		var linkobj = para.find('a:eq('+lnum+')');

		// Skip to next paragraph if link not found
		if (!linkobj || linkobj.length == 0) {
			return findLink(html, pnum);
		}

		// Strip /wiki/ from link so its easier to verify
		var link = linkobj.attr('href').replace('/wiki/','');

		// Increment link # for next loop
		lnum += 1;

		// Verify link formats
		if (link.substr(0, 1) === '#') badlink = true;
		else if (link.substr(0, 2) === '//') badlink = true;
		else if (link.substr(0, 5) === 'http:') badlink = true;
		else if (link.indexOf('File:') !== -1) badlink = true;
		else if (link.indexOf('Wikipedia:') !== -1) badlink = true;
		else badlink = false;

	} while(badLink);

	return link;
};
