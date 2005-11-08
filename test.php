#!/usr/bin/env php
<?php
require_once($_ENV['HOME'] . '/public_html/wp-config.php');
require_once('ical-events.php');

if (count($argv) < 2) die($argv[0] . " URL [TIME] [NUMBER]\n");

$url  = $argv[1];
$time = (isset($argv[2]) ? $argv[2] : time());
$num  = (isset($argv[3]) ? $argv[3] : 5);

ICalEvents::display_events($url, $time, NULL, $num);
?>
