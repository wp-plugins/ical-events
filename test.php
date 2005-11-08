#!/usr/bin/env php
<?php
require_once($_ENV['HOME'] . '/public_html/wp-config.php');
require_once('ical-events.php');

if (count($argv) < 2) die($argv[0] . " URL [NUMBER]\n");

ICalEvents::display_events($argv[1], time(), NULL, (isset($argv[2]) ? $argv[2] : 5));
?>
