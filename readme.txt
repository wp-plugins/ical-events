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

== Frequently Asked Questions ==

= How can I control the output of this plugin? =

The `display_events` function takes the following arguments:
* URL: The URL of the iCal file.
* Start time (optional): Only events from this time forward are displayed. If not specified, the earliest events are displayed.
* End time (optional): Only events before this time are displayed.
* Number of events (optional): The maximum number of events to display.
* Date format (optional): The format string used to format dates (see <a href="http://php.net/strftime">strftime documentation</a>). Default: `%a %b %d`.
* Time format (optional): The format string used to format times. Default: `%I:%M %p`.
* Before (optional): HTML or text to display before each event. Default: `<li>`.
* After (optional): HTML or text to display after each event. Default: `</li>`.
* Echo (optional): Whether or not to directly display the events. Default: `true`.

= How often is the calendar checked for new events? =

Once a day. You can change this in the `cache_url` function. Eventually, this might be an option available from the administration screens.

= Where can I find iCal files? =

There are many iCal sources, such as:
* <a href="http://www.apple.com/ical/library/">Apple's iCal library</a>
* <a href="http://www.icalshare.com/">iCalShare</a>
