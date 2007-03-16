=== iCal Events ===
Contributors: dwc
Tags: calendar, events
Requires at least: 2.0
Tested up to: 2.2
Stable tag: trunk

Display upcoming events from a shared calendar.

== Description ==

Fetch and display events from an iCalendar (`.ics`) URL in your blog.

This plugin uses [import_ical.php](http://cvs.sourceforge.net/viewcvs.py/webcalendar/webcalendar/import_ical.php?rev=HEAD) from the [WebCalendar](http://sourceforge.net/projects/webcalendar/) project. A slightly modified version of their parser is provided with this plugin.

== Installation ==

1. Upload `ical-events.php` and `import_ical.php` to your plugins folder, usually `wp-content/plugins`.
2. Activate the plugin on the Plugins screen.
3. Place the following in your template:

`<?php ICalEvents::display_events('url=http://www.ufl.edu/calendar/ufCalendar.ics&limit=3&gmt_start=' . time()); ?>`

This example displays three events from the University of Florida calendar, from the current time forward. For more information, see below.

== Frequently Asked Questions ==

= How can I control the output of this plugin? =

The `display_events` function takes the following arguments:

* `url`: The URL of the iCalendar file.
* `gmt_start` (optional): Only events from this time forward are displayed. If not specified, the earliest events are displayed.
* `gmt_end` (optional): Only events before this time are displayed.
* `limit` (optional): The maximum number of events to display.
* `date_format` (optional): The format string used to format dates (see [strftime documentation](http://php.net/strftime)). Default: `%a %b %e`.
* `time_format` (optional): The format string used to format times. Default: `%l:%M %p`.
* `before` (optional): HTML or text to display before each event. Default: HTML `li` start tag.
* `after` (optional): HTML or text to display after each event. Default: HTML `li` end tag.
* `before_date` (optional): HTML or text to display before each event's date. Default: HTML `strong` start tag.
* `after_date` (optional): HTML or text to display after each event's date. Default: HTML `strong` end tag followed by `: ` (colon and space).
* `use_summary` (optional): Whether or not to use the event summary in the output. Default: `true`.
* `before_summary` (optional): HTML or text to display before each event's summary. Default: Empty string.
* `after_summary` (optional): HTML or text to display after each event's summary. Default: Empty string.
* `use_description` (optional): Whether or not to use the event description in the output. Default: `true`.
* `before_description` (optional): HTML or text to display before each event's description. Default: ` - `.
* `after_description` (optional): HTML or text to display after each event's description. Default: Empty string.
* `replace_newlines_with` (optional): String with which to replace newlines in the description. Default: HTML `br` tag.
* `use_location` (optional): Whether or not to use the event location in the output. If false, only the summary is used. Default: `true`.
* `before_location` (optional): HTML or text to display before each event's location. Default: ` (`.
* `after_location` (optional): HTML or text to display after each event's location. Default: `)`.
* `use_url` (optional): Whether or not to use the event URL in the output. If true, the event URL is made into a link around the event summary. Default: `true`.
* `echo` (optional): Whether or not to directly display the events. Default: `true`.

For example, if you want to hide the description and location, you could use something like the following:

`<?php ICalEvents::display_events('url=http://www.ufl.edu/calendar/ufCalendar.ics&limit=3&use_description=0&use_location=0&gmt_start=' . time()); ?>`

If you need more control over the output, use the `get_events` function, which takes the following arguments:

* `url`: The URL of the iCalendar file.
* `gmt_start` (optional): Only events from this time forward are displayed. If not specified, the earliest events are displayed.
* `gmt_end` (optional): Only events before this time are displayed.
* `limit` (optional): The maximum number of events to display.

The function returns an array of events, as parsed by `import_ical.php`. For example usage, refer to the `display_events` function in the plugin.

= How often is the calendar checked for new events? =

Once a day. You can change this using the `ICAL_EVENTS_CACHE_LIFETIME` near the top of the plugin.

= Does the plugin support repeating events? =

This plugin makes an attempt to support repeating events. However, not all recurrence rules are implemented in the parser. There may also be bugs in how the plugin interprets the parsed data.

= Where can I find iCalendar files? =

There are many iCalendar sources, such as:

* [Apple's iCal library](http://www.apple.com/ical/library/)
* [iCalShare](http://www.icalshare.com/)
* [Google Calendar](http://calendar.google.com/)

= My server does not support `fopen` on URLs. Can I still use this plugin? =

As of version 1.9, this plugin supports usage of cURL via WordPress' `wp_remote_fopen` function. Previous versions required the `url-cache` plugin for cURL support, but this is no longer the case.
