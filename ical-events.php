<?php
/*
Plugin Name: iCal Events
Version: 1.4
Plugin URI: http://dev.webadmin.ufl.edu/~dwc/2005/03/10/ical-events-plugin/
Description: Display events from an iCal source. Requires <a href="http://cvs.sourceforge.net/viewcvs.py/webcalendar/webcalendar/import_ical.php?rev=HEAD">import_ical.php</a> from the <a href="http://sourceforge.net/projects/webcalendar/">WebCalendar</a> project.
Author: Daniel Westermann-Clark
Author URI: http://dev.webadmin.ufl.edu/~dwc/
*/

require('import_ical.php');

/*
 * As defined by import_ical.php, though not exported...
 */
$ICAL_EVENTS_REPEAT_INTERVALS = array(
	1 => 24 * 60 * 60,        // Daily
	2 => 7 * 24 * 60 * 60,    // Weekly
	5 => 365 * 25 * 60 * 60,  // Yearly
);

if (! class_exists('ICalEvents')) {
	class ICalEvents {
		/*
		 * Display up to the specified number of events that fall within
		 * the specified range on the specified calendar.  All
		 * constraints are optional.
		 */
		function display_events($url, $gmt_start = null, $gmt_end = null, $number_of_events = null, $date_format = '%a %b %e', $time_format = '%l:%M %p', $before = '<li>', $after = '</li>', $before_date = '<strong>', $after_date = '</strong>', $before_description = '', $after_description = '', $echo = true) {
			$filename = ICalEvents::cache_url($url);
			$events = parse_ical($filename);
			$events = ICalEvents::constrain($events, $gmt_start, $gmt_end, $number_of_events);

			$output = '';
			foreach ($events as $event) {
				$output .= $before;
				$output .= $before_date;
				if (ICalEvents::is_all_day($event['StartTime'], $event['EndTime'])) {
					$output .= htmlentities(strftime($date_format, $event['StartTime']));
				}
				else {
					$output .= htmlentities(ICalEvents::format_date_range($event['StartTime'], $event['EndTime'], $date_format, $time_format));
				}
				$output .= $after_date;
				$output .= $before_description;
				$output .= htmlentities(ICalEvents::format_event_description($event['Summary'], $event['Description']));
				$output .= $after_description;
				$output .= "<!-- " . htmlentities($event['UID']) . " -->";
				$output .= $after . "\n";
			}

			if ($echo) {
				echo $output;
			}

			return $output;
		}

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
		 * Cache the specified URL and return the name of the
		 * destination file.
		 */
		function cache_url($url) {
			$filename = ICalEvents::get_cache_path() . basename($url);

			if (! file_exists($filename) or time() - filemtime($filename) >= 24 * 60 * 60) {
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
		function constrain($events, $gmt_start = null, $gmt_end = null, $number_of_events = null) {
			$events = ICalEvents::sort_by_key($events, 'StartTime');

			$constrained = array();
			$count = 0;
			foreach ($events as $event) {
				if (ICalEvents::falls_between($event, $gmt_start, $gmt_end)) {
					$constrained[] = $event;
				}
				else if (isset($event['Repeat'])) {
					$repeats = ICalEvents::get_repeats_between($event, $gmt_start, $gmt_end);
					foreach ($repeats as $repeat) {
						$constrained[] = $repeat;
					}
				}
			}

			// TODO: This really should be done in the loop
			if ($number_of_events) {
				$constrained = array_splice($constrained, 0, $number_of_events);
			}

			return $constrained;
		}

		/*
		 * Sort the specified associative array by the specified key.
		 * Originally from
		 * http://us2.php.net/manual/en/function.usort.php.
		 */
		function sort_by_key($data, $key) {
			// Reverse sort
			$compare = create_function('$a, $b', 'if ($a["' . $key . '"] == $b["' . $key . '"]) { return 0; } else { return ($a["' . $key . '"] < $b["' . $key . '"]) ? -1 : 1; }');
			usort($data, $compare);

			return $data;
		}

		/*
		 * Return true iff the specified event falls between the given
		 * start and end times.
		 */
		function falls_between($event, $gmt_start, $gmt_end) {
			return ((! $gmt_start or $event['StartTime'] >= $gmt_start)
				and (! $gmt_end or $event['EndTime'] <= $gmt_end));
		}

		/*
		 * If the specified event repeats between the given start and
		 * end times, return one or more nonrepeating events at the
		 * corresponding times.
		 */
		function get_repeats_between($event, $gmt_start, $gmt_end) {
			global $ICAL_EVENTS_REPEAT_INTERVALS;

			/*
			echo "get_repeat_between: event = [\n";
			print_r($event);
			echo "], gmt_start = [$gmt_start], gmt_end = [$gmt_end]\n";
			*/

			$rr = $event['Repeat'];
			if ($gmt_end and $gmt_end >= $rr['EndTime']) return;

			$duration = 0;
			if (isset($event['EndTime'])) {
				$duration = $event['EndTime'] - $event['StartTime'];
			}

			$repeats = array();
			if (isset($ICAL_EVENTS_REPEAT_INTERVALS[$rr['Interval']])) {
				$interval    = $ICAL_EVENTS_REPEAT_INTERVALS[$rr['Interval']] * $rr['Frequency'];
				$repeat_days = preg_split('//', $rr['RepeatDays'], -1, PREG_SPLIT_NO_EMPTY);

				$current_time = $event['StartTime'];
				while ($current_time <= $rr['EndTime']) {
					$repeat = $event;
					unset($repeat['Repeat']);

					$repeat['StartTime'] = $current_time;
					if ($duration > 0) {
						$repeat['EndTime'] = $current_time + $duration;
					}

					if (ICalEvents::falls_between($repeat, $gmt_start, $gmt_end)) {
						$repeats[] = $repeat;
					}

					// TODO: Handle repeat days
					// TODO: Handle exceptions

					$current_time += $interval;
				}
			}
			else {
				echo "Unknown repeat interval: ${rr['Interval']}";
			}

			return $repeats;
		}

		/*
		 * Return a string representing the specified date range.
		 */
		function format_date_range($gmt_start, $gmt_end, $date_format, $time_format, $separator = ' - ') {
			$output = '';

			if (ICalEvents::is_today($gmt_start)) {
				$output .= strftime($time_format, $gmt_start);
			}
			else {
				$output .= strftime("$date_format $time_format", $gmt_start);
			}

			if ($gmt_start != $gmt_end) {
				$output .= $separator;
				if (ICalEvents::is_today($gmt_end) or ICalEvents::is_same_day($gmt_start, $gmt_end)) {
					$output .= strftime($time_format, $gmt_end);
				}
				else {
					$output .= strftime("$date_format $time_format", $gmt_end);
				}
			}

			return $output;
		}

		/*
		 * Given a time value (as seconds since the epoch), return true
		 * iff the time falls on the current day.
		 */
		function is_today($gmt) {
			return ICalEvents::is_same_day(time(), $gmt);
		}

		/*
		 * Return true iff the two times span exactly 24 hours, from
		 * midnight one day to midnight the next.
		 */
		function is_all_day($gmt1, $gmt2) {
			$local1 = localtime(($gmt1 <= $gmt2 ? $gmt1 : $gmt2), 1);
			$local2 = localtime(($gmt1 <= $gmt2 ? $gmt2 : $gmt1), 1);

			return (abs($gmt2 - $gmt1) == 24 * 60 * 60
				and $local1['tm_hour'] == $local2['tm_hour']
				and $local1['tm_hour'] == 0
				and $local1['tm_year'] == $local2['tm_year']);
		}

		/*
		 * Return true iff the two specified times fall on the same day.
		 */
		function is_same_day($gmt1, $gmt2) {
			$local1 = localtime($gmt1, 1);
			$local2 = localtime($gmt2, 1);

			return ($local1['tm_mday'] == $local2['tm_mday']
				and $local1['tm_mon'] == $local2['tm_mon']
				and $local1['tm_year'] == $local2['tm_year']);
		}

		/*
		 * Return a string describing an event with the specified
		 * summary and description.
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
