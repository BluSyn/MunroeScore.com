#!/usr/bin/php
<?php
/*
 * Get Munroe Score from wikipedia
 *
 * Written by Steven Bower; 2012
 * This program is available free of charge.
 * I make no license claims, as I find that pointless.
 */

$start_time = microtime(TRUE);

// Set user-agent string to something descriptive
ini_set('user_agent', 'MunroeScoreBot/0.1; (+http://munroescore.com)');

$cfg = array(
	// Pages that count as a 'Final' page to end on
	'endpages' => array(
		'Philosophy'
	),

	// Base URL for wikipedia
	// For bandwidth and usability, mobile version is the best
	'baseurl' => 'http://en.m.wikipedia.org/wiki/',

	// Limit number of pages we search to throttle bandwidth
	'limit' => 30,
);

// Include DOM library; incredibly simple
// A more powerful DOM library may be needed later on
require 'lib/simple_html_dom.php';

// Take input from command line
// if nothing is entered, default to "Mathematics"
// since is has a small score
$page = isset($argv[1]) ? implode('_',array_slice($argv,1)) : 'Mathematics';

// Escape input
$page = str_replace(' ', '_', $page);
$page = urlencode($page);

print 'Page: '.$page ."\n";

// Track number of jumps (minimum is 1)
$score = 1;

// Start process
$run = get_wiki($page, $score);

/*
 * Gets wikipedia page and parses for links
 */
function get_wiki($page, $score) {
	global $cfg;

	// Throttle searching so we don't piss of wikipedia's servers
	if ($score >= $cfg['limit']) {
		print 'Score as gone past '.$cfg['limit'].'; Program halted.'."\n";
		return;
	}

	print 'Loading: '.$page.'... ';

	$dom = file_get_html($cfg['baseurl'].$page);

	// Get main body element where content is
	$contents = $dom->find('#bodyContent .mw-content-ltr');
	$contents = $contents[0];

	// Search contents for first valid link
	$next = find_link($contents);

	// Unset dom/content vars to free up memory
	$dom->clear();

	// Print error if no link found
	if ($next === FALSE) {
		print 'Error: Could not locate next link.'."\n";
		return;
	}

	// If found link is the final link in the chain display success
	if (array_search($next, $cfg['endpages']) !== FALSE) {
		print 'Final link: '.$next."\n";
		print "\n".'End point reached.'."\n";
		print 'Munroe Score: '.$score."\n";
		return;
	}
	else {
		// Go to next link in chain
		print 'Next link found: '.$next."\n";
		get_wiki($next, $score+1);
	}

	return;
}

/*
 * This looks for the first link in a wikipedia article
 * that is not italic or inside parantheses
 *
 * $content is the object of main content area (#bodyContent .mw-content-ltr)
 * $num is the paragraph number to look at for next link (default 0)
 */
function find_link($content, $num = 0) {

	// At a certain point we give up looking for links
	if ($num >= 20) return FALSE;

	// Get specified paragraph from content
	$para = $content->find('p',$num);

	/*
	 * Ensure this paragraph is a child of the main div
	 * this prevents links that show up in the side tables
	 *
	 * NOTE: A better solution would be to remove tables from DOM,
	 * but existing DOM library does not support this
	 */
	if ($para->parent()->class !== 'mw-content-ltr') {
		return find_link($content, $num+1);
	}

	// Replace parathenses inside href tags with proper url char code
	// to prevent their deletion when parans are removed
	preg_match_all('/href="\/wiki\/[0-9A-Z_\(\)]+"/i', $para, $ma);
	foreach ($ma[0] AS $match) {
		$new = str_replace('(', '%28', str_replace(')', '%29', $match));
		$para = str_replace($match, $new, $para);
	}

	// Remove all parantheses from content before searching for link
	do {
		$para = preg_replace('/\([^\)\(]+\)/i', '', $para);
		$para = preg_replace('/\[[^\]\[]+\]/i', '', $para);
	} while(strpos($para, '(') !== FALSE);

	// Parse raw string for link
	$para = str_get_html($para);

	// Find all links in paragraph to verify and count
	$links = @$para->find('a');

	// If no links are found, go to next paragraph
	if (!$links) return find_link($content, $num+1);

	$numlinks = count($links);

	// Start going through each link until we find the proper "first"
	$currlink = 0;
	$badlink = FALSE;
	do {
		// If we go past the total number of links,
		// then skip to next paragraph
		if ($currlink >= $numlinks) {
			return find_link($content, $num+1);
		}

		// Get first a:href attribute
		$link = @$para->find('a',$currlink)->href;

		// Skip to next paragraph if link not found
		if (!$link) return find_link($content, $num+1);

		// Strip /wiki/ from link so its easier to verify
		$link = str_replace('/wiki/','',$link);
		++$currlink;

		// Verify link before continuing
		if (substr($link, 0, 1) === '#') $badlink = TRUE;
		elseif (substr($link, 0, 2) === '//') $badlink = TRUE;
		elseif (substr($link, 0, 5) === 'http:') $badlink = TRUE;
		elseif (strpos($link, 'File:') !== FALSE) $badlink = TRUE;
		elseif (strpos($link, 'Wikipedia:') !== FALSE) $badlink = TRUE;
		else $badlink = FALSE;

	} while ($badlink);

	// Link has been found, pass it along
	return $link;
}

$end_time = microtime(TRUE);
print "\n".($end_time - $start_time).' seconds'."\n";

// EOF
?>
