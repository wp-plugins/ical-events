<?php
/*
Plugin Name: iCal Events
Version: 1.0
Plugin URI: http://dev.webadmin.ufl.edu/~dwc/2005/03/10/ical-events-plugin/
Description: Display events from an iCal source. Requires <a href="http://cvs.sourceforge.net/viewcvs.py/webcalendar/webcalendar/import_ical.php?rev=HEAD">import_ical.php</a> from the <a href="http://sourceforge.net/projects/webcalendar/">WebCalendar</a> project.
Author: Daniel Westermann-Clark
Author URI: http://dev.webadmin.ufl.edu/~dwc/
*/

require('import_ical.php');


if (! class_exists('ICalEvents')) {
	class ICalEvents {
		/*
		 * Attempt to create the cache directory if it doesn't exist.
		 * Return the path if successful.
		 */
		function get_cache_path() {
			$cache_path = trailingslashit(ABSPATH . 'wp-content/ical-events-cache');

			if (! file_exists($cache_path)) {
				if (is_writable(dirname($cache_path))) {
					if (! mkdir($cache_path, 0777)) {
						die("Error creating cache directory ($cache_path)");
					}
				}
				else {
					die("Your cache directory (<code>$cache_path</code>) needs to be writable for this plugin to work. Double-check it. <a href='" . get_settings('siteurl') . "/wp-admin/plugins.php?action=deactivate&amp;plugin=ical-events.php'>Deactivate the iCal Events plugin</a>.");
				}
			}

			return $cache_path;
		}

		/*
		 * Display up to the specified number of events that fall within the specified
		 * range on the specified calendar.  All constraints are optional.
		 */
		function display_events($url, $gmt_start = NULL, $gmt_end = NULL, $number_of_events = NULL, $date_format = '%a %b %d', $time_format = '%I:%M %p', $before = '<li>', $after = '</li>', $echo = true) {
			$filename = ICalEvents::cache_url($url);
			$ical = parse_ical($filename);
			$events = ICalEvents::constrain($ical, $gmt_start, $gmt_end, $number_of_events);

			$output = '';
			foreach ($events as $event) {
				$output .= $before;
				$output .= htmlentities(ICalEvents::format_date_range($event['StartTime'], $event['EndTime'], $date_format, $time_format));
				$output .= htmlentities(ICalEvents::format_event_description($event['Summary'], $event['Description']));
				$output .= "<!-- " . htmlentities($event['UID']) . " -->";
				$output .= $after . "\n";
			}

			if ($echo) {
				echo $output;
			}

			return $output;
		}

		/*
		 * Cache the specified URL and return the name of the destination file.
		 */
		function cache_url($url) {
			$filename = ICalEvents::get_cache_path() . basename($url);

			if ((! file_exists($filename)) or (time() - filemtime($filename) >= 24 * 60 * 60)) {
				$src  = fopen($url, 'r') or die("Error opening $url");
				$dest = fopen($filename, 'w') or die("Error opening $filename");

				while ($data = fread($src, 8192)) {
					fwrite($dest, $data);
				}

				fclose($src);
				fclose($dest);
			}

			return $filename;
		}

		/*
		 * Constrain the list of events to those which fall between the
		 * specified start and end time, up to the specified number of
		 * events.
		 */
		function constrain($ical, $gmt_start = NULL, $gmt_end = NULL, $number_of_events = NULL) {
			$events = array();

			$ical = ICalEvents::sort_by_key($ical, 'StartTime');
			$count = 0;
			foreach ($ical as $event) {
				if (((! $gmt_start) or ($event['StartTime'] >= $gmt_start))
				    and ((! $gmt_end) or ($event['EndTime'] <= $gmt_end))
				    and (++$count <= $number_of_events)) {
					array_push($events, $event);
				}
			}

			return $events;
		}

		/*
		 * Sort the specified associative array by the specified key.
		 * Originally from http://us2.php.net/manual/en/function.usort.php.
		 */
		function sort_by_key($data, $key) {
			// Reverse sort
			$compare = create_function('$a, $b', 'if ($a["'.$key.'"] == $b["'.$key.'"]) { return 0; } else { return ($a["'.$key.'"] < $b["'.$key.'"]) ? -1 : 1; }');
			usort($data, $compare);

			return $data;
		}

		/*
		 * Return a string representing the specified date range.
		 */
		function format_date_range($gmt_start, $gmt_end, $date_format, $time_format, $separator = ' - ') {
			$output = '';

			$output .= ICalEvents::format_date($gmt_start, $date_format, $time_format);
			if ($gmt_start != $gmt_end) {
				$output .= $separator . ICalEvents::format_date($gmt_end, $date_format, $time_format);
			}

			return $output;
		}

		/*
		 * Return a string representing the specified date.
		 */
		function format_date($gmt_time, $date_format, $time_format) {
			$output = '';

			$local_time = localtime($gmt_time, 1);
			if (ICalEvents::time_is_today($gmt_time)) {
				$output = strftime("$time_format", $gmt_time);
			}
			else if ($local_time['tm_hour'] != 0) {
				$output = strftime("$date_format $time_format", $gmt_time);
			}
			else {
				$output = strftime("$date_format", $gmt_time);
			}

			return $output;
		}

		/*
		 * Given a time value (as seconds since the epoch), return true iff the time
		 * falls on the current day.
		 */
		function time_is_today($gmt_time) {
			$current_time = localtime(time(), 1);
			$local_time = localtime($gmt_time, 1);

			return ($current_time['tm_mday'] == $local_time['tm_mday']
				and $current_time['tm_mon'] == $local_time['tm_mon']
				and $current_time['tm_year'] == $local_time['tm_year']);
		}

		/*
		 * Return a string describing an event with the specified summary and
		 * description.
		 */
		function format_event_description($summary, $description, $ignore_tokens = ', . "') {
			$output = '';

			$clean_summary = str_replace(explode(' ', $ignore_tokens), ' ', $summary);
			$clean_description = str_replace(explode(' ', $ignore_tokens), ' ', $description);

			if ($description) {
				$output .= ": ";
				if (strpos($clean_description, $clean_summary) === false) {
					$output .= $summary . ". ";
				}
				$output .= $description;
			}
			else {
				$output .= ": " . $summary;
			}

			return $output;
		}
	}
}
?>
