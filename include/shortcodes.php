<?php

/**
 * This shortcode function to convert any datetime into the format exist in WP datetime options or to custom format you need
 * @param string $date
 *        	String accepted datetime only
 *
 * @param string $custom_format
 *        	String accepted Hijri or Gregorian formats only
 *        	The following characters are recognized to output Hijri Calendar in the format parameter string
 *        	<table><tr><th>format character</th><th>Description</th><th>Example Output</th></tr>
 *        	<tr><td>_j</td><td>Day of the hijri month without leading zeros 1 to 30</td><td>1-30</td></tr>
 *        	<tr><td>_d</td><td>Day of the hijri month with leading zeros 01 to 30</td><td>01-30</td></tr>
 *        	<tr><td>_S</td><td>English suffix for numbers (new in 2.3.0)</td><td>st, nd ,th</td></tr>
 *        	<tr><td>_z</td><td>The day of the year (starting from 0)</td><td>0-354</td></tr>
 *        	<tr><td>_F</td><td>A full textual representation of a month, such as Muharram or Safar</td><td>Muharram-Dhul Hijjah</td></tr>
 *        	<tr><td>_M</td><td>A short textual representation of a month, three letters(in Arabic same as _F)</td><td>Muh-Hij</td></tr>
 *        	<tr><td>_m</td><td>Numeric representation of a month, with leading zeros</td><td>01-12</td></tr>
 *        	<tr><td>_n</td><td>Numeric representation of a month, without leading zeros</td><td>1-12</td></tr>
 *        	<tr><td>_L</td><td>Whether it's a leap year</td><td>1 if it is a leap year, 0 otherwise</td></tr>
 *        	<tr><td>_Y</td><td>A full numeric representation of a year, 4 digits</td><td>1380 or 1436</td></tr>
 *        	<tr><td>_y</td><td>A two digit representation of a year</td><td>80 or 36</td></tr>
 *        	<tr><td colspan=3>These format character will overridden if langcode set to 'ar'</td></tr>
 *        	<tr><td>l, D</td><td>A full textual representation of the day of the week in Arabic</td><td>السبت-الجمعة </td></tr>
 *        	<tr><td>F</td><td>A full textual representation of a month, Syrian Name</td><td>كانون الثاني، شباط </td></tr>
 *        	<tr><td>M</td><td>A full textual representation of a month, English translated</td><td>يناير، فبراير</td></tr>
 *        	<tr><td>a</td><td>Lowercase Ante meridiem and Post meridiem in Arabic</td><td>ص ، م</td></tr>
 *        	<tr><td>A</td><td>Full Ante meridiem and Post meridiem in Arabic</td><td>صباحا ، مساء</td></tr>
 *        	</table>
 * @return string full date after customize the format
**/
add_shortcode( 'wp_hijri_date', 'wp_hijri_convert_custom_date_shortcode' );

function wp_hijri_convert_custom_date_shortcode( $atts, $content = "" ) {
  extract( shortcode_atts( array(
    'date' => date("Y-m-d"),
    'custom_format' => "D _Y/_m/_d\A\H (d-m-Y\A\D)",
  ), $atts ) );
  global $hijri;
  $datetime=date_create($date);
  if($datetime !== FALSE){
    $datetime->setTimezone(wp_timezone());
    $converted_date = $hijri->wp_hijri_format($datetime, $custom_format);
  } else {
    $converted_date = '-';
  }

  return $converted_date;
}

?>
