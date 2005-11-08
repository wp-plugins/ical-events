=== iCal Events ===
Tags: calendar, events
Contributors: dwc

A plugin for getting and displaying upcoming events from a shared calendar.

This plugin uses <a href="http://cvs.sourceforge.net/viewcvs.py/webcalendar/webcalendar/import_ical.php?rev=HEAD">import_ical.php</a> from the <a href="http://sourceforge.net/projects/webcalendar/">WebCalendar</a> project.

== Installation ==

1. Upload `ical-events.php` and `import_ical.php` to your plugins folder, usually `wp-content/plugins`.
2. Activate the plugin on the Plugins screen.
3. Place the following in your template:

`<?php ICalEvents::display_events('http://www.ufl.edu/calendar/ufCalendar.ics', time(), NULL, 3); ?>`

This example displays three events from the University of Florida calendar, from the current time forward. For more information, see below.

== Frequently Asked Questions ==

= How can I control the output of this plugin? =

The `display_events` function takes the following arguments:
* URL: The URL of the iCal file.
* Start time (optional): Only events from this time forward are displayed. If not specified, the earliest events are displayed.
* End time (optional): Only events before this time are displayed.
* Number of events (optional): The maximum number of events to display.
* Date format (optional): The format string used to format dates (see <a href="http://php.net/strftime">strftime documentation</a>). Default: `%a %b %e`.
* Time format (optional): The format string used to format times. Default: `%l:%M %p`.
* Before (optional): HTML or text to display before each event. Default: `<li>`.
* After (optional): HTML or text to display after each event. Default: `</li>`.
* Before date (optional): HTML or text to display before each event's date. Default: `<strong>`.
* After date (optional): HTML or text to display after each event's date. Default: `</strong>`.
* Before description (optional): HTML or text to display before each event's description. Default: Empty string.
* After description (optional): HTML or text to display after each event's description. Default: Empty string.
* Echo (optional): Whether or not to directly display the events. Default: `true`.

If you need more control over the output, use the `get_events` function, which takes the following arguments:
* URL: The URL of the iCal file.
* Start time (optional): Only events from this time forward are displayed. If not specified, the earliest events are displayed.
* End time (optional): Only events before this time are displayed.
* Number of events (optional): The maximum number of events to display.

The function returns an array of events, as parsed by `import_ical.php`. For example usage, refer to the `display_events` function in the plugin.

= How often is the calendar checked for new events? =

Once a day. You can change this in the `cache_url` function. Eventually, this might be an option available from the administration screens.

= Does the plugin support repeating events? =

This plugin makes an attempt to support repeating events. However, not all recurrence rules are implemented in the parser. There may also be bugs in how the plugin interprets the parsed data.

= Where can I find iCal files? =

There are many iCal sources, such as:
* <a href="http://www.apple.com/ical/library/">Apple's iCal library</a>
* <a href="http://www.icalshare.com/">iCalShare</a>
