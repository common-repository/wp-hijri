=== WP-Hijri ===
Tags: date, Hijri, Gregorian, Islamic, Makkah
Requires at least: 4.0
Requires PHP: 5.3
Tested up to: 6.6
Stable tag: 1.5.3
License: GPLv2 or later
Author: Saeed Hubaishan & Mohammad Okfie
Text Domain: wp_hijri
Domain Path: /lang
Contributors: okfie,hubaishan

Displays dates in Hijri, Gregorian or both your site, Allows to change URLs to Hijri, Contains Hijri Calendar and Archive widgets.


== Description ==
This plugin Allows you to display dates in Hijri, Gregorian or both your site. You can display the both Hijri and Gregorian dates in single format. You can Also remain using Gregorian Date Without need to inactivate the plugin. The plugin Allows you to change URLs to Hijri. And Contains Hijri Calendar and Archive widgets fully equivalent to original WordPress widgets.

= Features =
* Displays dates in posts and pages in Hijri, Gregorian or both (Hijri and Gregorian) by changing the date format.
* You can Also remain using Gregorian Date Without need to inactivate the plugin.
* Custom formats for (Hijri and Gregorian) dates.
* Allows you to force all dates in WordPress to return Hijri dates
* Allows you to adjust the start of Hijri month to sync with crescent sighting and the adjustments saved to database.
* Allows to use Hijri Calendar in URLs.
* Outputs the Hijri date in 20 international languages.
* Has Two algorithms for calculting the Hijri Date: ( Um Al Qura algorithm and Tabular algorithm ).
* Widget : Hijri Calendar
fully equivalent to original WordPress Calendar widget.  This widget can work togather with the original WordPress Calendar widget without any conflicts.
* Widget : Hijri Archives.
fully equivalent to original WordPress Archives widget. This widget can work togather with the original WordPress Archives widget without any conflicts.
* Shortcode : [wp_hijri_date]
This shortcode helps to add it in posts, pages or any post type to make it easy to convert the Gregorian date to custom format.
Ex.: [wp_hijri_date date="2019-06-03" lang="en" custom_format="D _d-_m-_Y (d-m-Y)"]
* Multisite support.

= Translations =
* Arabic
* English

== Installation ==

= Installation Automatically =
1. Click Plugins in the menu dashboard.
1. Click Add New.
1. Upload and choose "WP-Hijri.zip" file and activate directly.
1. After activated plugin you can see sub page of settings inside "Settings" menu.

= Installation Manually =
1. Download the plugin to your computer.
1. Unzip the file and upload it to the "/wp-content/plugins/" by using FTP or Cpanel.
1. Activate the plugin through the "Plugins" menu in WordPress dashboard.
1. After activated plugin you can see sub page of settings inside "Settings" menu.

== Screenshots ==
1. Settings Page.
2. Test Hijri date.
3. Custom widgets in sidebar for Hijri date.
4. Test all widgets for Hijri date.
5. Test for force all dates to return Hijri dates.
6. Add all formats Hijri date to general settings.


== Changelog ==
= 1.5.3 =
- Some minor fixes.
- uses new HijriDateLib v2.3.3.
- Tested with WP 6.6.X
= 1.5.2 =
- Some minor fixes.
- uses new HijriDateLib v2.3.2.
- Tested with WP 6.2.X
= 1.5.1 =
- Some minor fixes.
- Tested with WP 5.9
= 1.5.0 =
- Multisite support.
- Fix TimeZone with wp_date() function.
- Fix shortcode with TimeZone
- Throw shortcode `lang` parameter. Now the shortcode return formatted date with WP-Hijri language option for Hijri and with WP language for Gregorian.
- Some minor fixes.
- Tested with WP 5.5.1
= 1.4.0 =
- Tested with WP 5.3.X
- Handle new wp_date() function with handle_date_i18n()
= 1.3.1 =
- Some minor fixes.
= 1.3.0 =
- Tested with WP 5.X.X
- Added new shortcode that called [wp_hijri_date].
- Added custom format date in possible starts section.
- Some minor fixes.
= 1.2.0 =
- uses new HijriDateLib v2.3.0.
- fix bug when change WP language to un supported language while WP-Hijri language set to System Language.
- fix bug handling Hijri archive URL using M query var.
- some language corrections.
= 1.1.2 =
- Some incompatible syntaxes with php 5.3 were fixed.
= 1.1.1 =
- Some incompatible syntaxes with php 5.3 were fixed.
- Use HijriDateLib ver 2.2.1.
= 1.1.0 =
- Add a filter for new WP 4.4 title function.
- Calendar widget now send to Hijri Date URL.
- Some minor fixes.
= 1.0.0 =
- First released version.
