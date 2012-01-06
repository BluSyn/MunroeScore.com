<?php
/*
 * Get Munroe Score from wikipedia
 */

$cfg = array(
	// Pages that count as a 'Final' page to end on
	'endpages' => array(
		'Philosophy'
	),

	// Base URL for wikipedia
	'baseurl' => 'http://en.wikipedia.org/wiki/',

	// Limit number of pages we search to throttle bandwidth
	'limit' => 20,
);

require 'lib/simple_html_dom.php';

$start_time = microtime(TRUE);

//$page = 'Government';
$page = 'Thomas Jefferson';

// Escape input
$page = str_replace(' ', '_', $page);
$page = urlencode($page);

print 'Page: '.$page ."\n";

// Track number of jumps (minimum is 1)
$score = 1;

// Start process
$run = get_wiki($page, $score);

/*
 * Gets wikipedia page and parses for link
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
	//unset($dom, $contents);

	// Print error if no link found
	if ($next === FALSE) {
		print 'Error: Could not locate next link.'."\n";
		return;
	}

	// If found link is the final link in the chain display success
	if (array_search($next, $cfg['endpages']) !== FALSE) {
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
 * that is no italics or inside parantheses
 *
 * $content is the text of main content area (#bodyContent .mw-content-ltr)
 * $num is the paragraph number to look at for next link (default 0)
 */
function find_link($content, $num = 0) {

	// At a certain point we give up looking for links
	if ($num >= 5) return FALSE;

	// Get specified paragraph from content
	$para = $content->find('p',$num);

	// Replace parathenses inside href tags with proper url char code
	preg_match_all('/href="\/wiki\/[0-9A-Z_\(\)]+"/i', $para, $ma);
	foreach ($ma[0] AS $match) {
		$new = str_replace('(', '%28', str_replace(')', '%29', $match));
		$para = str_replace($match, $new, $para);
	}

	// Remove all parantheses from content before searching for link
	do {
		$para = preg_replace('/\([^\)\(]+\)/i', '', $para);
	} while(strpos($para, '(') !== FALSE);

	// Parse raw string for link
	$para = str_get_html($para);

	// Get first a:href attribute
	$link = @$para->find('a',0)->href;

	// If no link is found, go to next paragraph
	if (!$link) return find_link($content, $num+1);
	else return str_replace('/wiki/','',$link);
}

$end_time = microtime(TRUE);
print "\n".($end_time - $start_time).' seconds'."\n";

// EOF
?>
