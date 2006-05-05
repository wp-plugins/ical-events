#!/usr/bin/env php
<?php
error_reporting(E_ALL);

$_ENV['ICAL_EVENTS_DEBUG'] = true;

require_once($_ENV['HOME'] . '/public_html/wp-config.php');
require_once('ical-events.php');

if (count($argv) < 2) die("$argv[0] ARGS\n");

// Parse the arguments
$pairs  = array_splice($argv, 1);
$args = array();
foreach ($pairs as $pair) {
	list($key, $value) = split('=', $pair);
	$args[$key] = $value;
}

// Remove the cached file
if ($args['url']) {
	$file = ICalEvents::get_cache_file($args['url']);
	if (file_exists($file)) {
		unlink($file);
	}
}

// Build a query string
$encoded = array();
foreach ($args as $key => $value) {
	$encoded[] = urlencode($key) . '=' . urlencode($value);
}

ICalEvents::display_events(implode('&', $encoded));
?>
