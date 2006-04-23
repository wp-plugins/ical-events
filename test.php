#!/usr/bin/env php
<?php
error_reporting(E_ALL);

require_once($_ENV['HOME'] . '/public_html/wp-config.php');
require_once('ical-events.php');

if (count($argv) < 2) die($argv[0] . "QUERY_STRING\n");

$args = array();
parse_str($argv[1], $args);

$pairs = array();
foreach ($args as $key => $value) {
	$pairs[] = urlencode($key) . '=' . urlencode($value);
}

ICalEvents::display_events(implode('&', $pairs));
?>
