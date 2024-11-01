<?php

/*
 * Plugin Name: WP-Hijri
 * Description: Displays dates in Hijri, Gregorian or both your site, Allows to change URLs to Hijri, Contains Hijri Calendar and Archive widgets.
 * Text Domain: wp-hijri
 * Domain Path: /lang
 * License: GPLv2 or later
 * Network:	true
 * Version: 1.5.3
 */
define("THE_WP_HIJRI_PATH", dirname(__FILE__) . '/');
include_once THE_WP_HIJRI_PATH . 'include/hijri.class.php';
include_once THE_WP_HIJRI_PATH . 'include/hijri-widgets.php';
include_once THE_WP_HIJRI_PATH . 'include/shortcodes.php';

if (!class_exists('WP_Hijri')) {

	class WP_Hijri
	{
		/*
		 * @var Hijri/Calendar
		 */
		public $hcal;
		/*
		 * @var string
		 */
		public $lang;
		/*
		 * @var array all wp-Hijri Settings:
		 * $langcode string
		 * $umalqura bool
		 * $adj_data intger[]
		 * $force_hijri bool
		 * $hijri_url bool
		 *
		 */
		public $settings = array('langcode' => '', 'umalqura' => TRUE, 'adj_data' => array(), 'force_hijri' => FALSE, 'hijri_url' => FALSE);

		/*
		 *
		 * Register the hooks for functions.
		 *
		 */
		public function __construct()
		{
			global $wp_locale, $wp_version;
			// settings
			if ( is_multisite() )
			{
				$mysettings = json_decode(get_site_option('Hijri_Settings'), TRUE);
			} else {
				$mysettings = json_decode(get_option('Hijri_Settings'), TRUE);
			}

			if (is_array($mysettings)) {
				$this->settings = array_merge($this->settings, $mysettings);
			}

			// plugin
			if ( ! is_multisite() )
			{
				add_action('admin_menu', array($this, 'hijri_add_options'));
			} elseif ( is_multisite() &&  is_main_site() ) {
				add_action('network_admin_menu', array($this, 'hijri_add_options'));
			}
			add_action('plugins_loaded', array($this, 'lang_wp_hijri'));
			// Add plugin meta links
			add_filter('plugin_row_meta', array($this, 'meta_links_wp_hijri'), 10, 2);
			// filter date function
			if (version_compare($wp_version,'5.3.0','>=')) {
				add_filter('wp_date', array($this, 'handle_wp_date'), 10, 4);
			} else {
				add_filter('date_i18n', array($this, 'handle_date_i18n'), 10, 4);
			}
			// add Hijri Date formates
			add_filter('date_formats', array($this, 'handle_date_formats'), 10, 1);
			// manage archive titles
			add_filter('wp_title_parts', array($this, 'hijri_wp_title_parts'), 10, 1);
			add_filter('get_the_archive_title', array($this, 'get_the_archive_title_fl'), 10, 1);
			// for 4.4
			add_filter('document_title_parts', array($this, 'document_title_parts_fl'), 10, 1);

			// manage hijri queries
			add_action('pre_get_posts', array($this, 'pre_get_posts_action'));
			add_filter('query_vars', array($this, 'query_vars_fl'));
			add_filter('posts_where', array($this, 'hijri_posts_where_fl'));
			add_filter('posts_results', array($this, 'posts_results_fl'), 10, 2);
			// manage hijri url
			if ($this->settings['hijri_url']) {
				add_filter('pre_post_link', array($this, 'hijri_pre_post_link_fl'), 10, 2);
			}

			// calendar Widget cache actions
			add_action('save_post', array($this, 'delete_get_hijri_calendar_cache'));
			add_action('delete_post', array($this, 'delete_get_hijri_calendar_cache'));
			add_action('update_option_start_of_week', array($this, 'delete_get_hijri_calendar_cache'));
			add_action('update_option_gmt_offset', array($this, 'delete_get_hijri_calendar_cache'));

			// set language
			$this->lang = $this->settings['langcode'];
			if (empty($this->lang)) {
				add_action('after_setup_theme', array($this, 'set_lang'));
			}
			// init hcal
			$this->hcal = new \hijri\Calendar(array('umalqura' => $this->settings['umalqura'], 'adj_data' => $this->settings['adj_data']));
		}

		public static function activate()
		{
			update_option('date_format', 'D _j _F _Y\A\H j-n-Y\A\D');
			add_option('Hijri_Settings');
		}

		public static function deactivate()
		{
			update_option('date_format', __('F j, Y'));
		}

		/**
		 * Add plugin meta links
		 */
		public static function meta_links_wp_hijri($links, $file)
		{
			$plugin = plugin_basename(__FILE__);
			// Check plugin
			if ($file === $plugin) {
				unset($links[2]);
				$links[] = __('Author: <a target="_blank" href="https://twitter.com/hubaishan">Saeed Hubaishan</a> & <a target="_blank" href="https://twitter.com/mohdokfie">Mohammad Okfie</a>', 'wp-hijri') . '</a>';
				$links[] = '<a href="https://github.com/hubaishan/HijriDateLib/" target="_blank">' . __('Hijri Date lib', 'wp-hijri') . '</a>';
			}
			return $links;
		}

		/**
		 * setting langcode if system language selected
		 */
		public function set_lang()
		{
			global $wp_locale;
			if (empty($this->lang)) {
				$lang = '';
				$WPLANG = get_option('WPLANG');
				if (empty($WPLANG)) {
					$WPLANG = 'en';
				}
				if (in_array($WPLANG, \hijri\Calendar::$supported_languages)) {
					$lang = $WPLANG;
				} elseif (in_array(substr($WPLANG, 0, 2), \hijri\Calendar::$supported_languages)) {
					$lang = substr($WPLANG, 0, 2);
				} elseif ($wp_locale->is_rtl()) {
					$lang = 'ar';
				} else {
					$lang = 'en';
				}

				$this->lang = $lang;
			}
		}

		/**
		 * Load langauge file.
		 */
		function lang_wp_hijri()
		{
			load_plugin_textdomain('wp-hijri', false, dirname(plugin_basename(__FILE__)) . '/lang/');
			__('WP-Hijri', 'wp-hijri');
			__('Displays dates in Hijri, Gregorian or both your site, Allows to change URLs to Hijri, Contains Hijri Calendar and Archive widgets.', 'wp-hijri');
		}

		/**
		 * Create new option page in settings menu.
		 */
		function hijri_add_options()
		{
			if ( is_multisite() && is_super_admin() && is_main_site() ) {
				$hijri_admin_page = add_submenu_page( 'settings.php', __('Settings Date', 'wp-hijri'), __('WP-Hijri', 'wp-hijri'), 'manage_options', 'settings_wp_hijri', array($this, 'page_menu_wp_hijri'));
				add_action('load-' . $hijri_admin_page, array($this, 'admin_save_options'));
			} elseif ( ! is_multisite() ) {
				$hijri_admin_page = add_options_page(__('Settings Date', 'wp-hijri'), __('WP-Hijri', 'wp-hijri'), 'manage_options', 'settings_wp_hijri', array($this, 'page_menu_wp_hijri'));
				add_action('load-' . $hijri_admin_page, array($this, 'admin_save_options'));
			}

		}

		/**
		 * Include page for settings.
		 */
		function page_menu_wp_hijri()
		{
			include_once THE_WP_HIJRI_PATH . 'include/admin_form.php';
		}

		function delete_get_hijri_calendar_cache()
		{
			wp_cache_delete('get_hijri_calendar', 'calendar');
		}

		function query_vars_fl($public_query_vars)
		{
			$public_query_vars[] = 'hyear';
			$public_query_vars[] = 'hmonth';
			$public_query_vars[] = 'hday';
			$public_query_vars[] = 'hijri_archive';
			return $public_query_vars;
		}

		function hijri_wp_title_parts($title_parts)
		{
			global $wp_locale, $wp_query;
			$qv = $wp_query->query_vars;
			if (empty($qv['hijri_archive'])) {

				return $title_parts;
			}
			$m = $qv['m'];
			$year = (isset($qv['hyear'])) ? (int) $qv['hyear'] : '';
			$month = (isset($qv['hmonth'])) ? (int) $qv['hmonth'] : '';
			$day = (isset($qv['hday'])) ? (int) $qv['hday'] : '';
			$new_title_parts = array();
			$new_title_parts[] = $year . (($wp_locale->is_rtl()) ? ('هـ') : ('AH'));
			if (!empty($month)) {
				$new_title_parts[] = $this->hcal->month_name($month, $this->lang);
				if (!empty($day)) {
					$new_title_parts[] = $day;
				}
			}
			return $new_title_parts;
		}

		// wp >=4.4.0
		function document_title_parts_fl($title)
		{
			global $wp_locale, $wp_query;
			global $wp_query;
			if (!empty($wp_query->query_vars['hijri_archive'])) {
				if (is_year()) {
					$title['title'] = get_the_date($this->convert_format_to_hijri(_x('Y', 'yearly archives date format'), TRUE));
				} elseif (is_month()) {
					$title['title'] = get_the_date($this->convert_format_to_hijri(_x('F Y', 'monthly archives date format'), TRUE));
				} elseif (is_day()) {
					$title['title'] = get_the_date($this->convert_format_to_hijri(get_option('date_format'), TRUE));
				}
			}
			return $title;
		}

		function get_the_archive_title_fl($title)
		{
			global $wp_query;
			if (!empty($wp_query->query_vars['hijri_archive'])) {

				if (is_year()) {
					$title = sprintf(__('Year: %s'), get_the_date($this->convert_format_to_hijri(_x('Y', 'yearly archives date format'), TRUE)));
				} elseif (is_month()) {
					$title = sprintf(__('Month: %s'), get_the_date($this->convert_format_to_hijri(_x('F Y', 'monthly archives date format'), TRUE)));
				} elseif (is_day()) {
					$title = sprintf(__('Day: %s'), get_the_date($this->convert_format_to_hijri(_x('F j, Y', 'daily archives date format'), TRUE)));
				}
			}
			return $title;
		}

		function hijri_posts_where_fl($where)
		{
			global $wp_query, $wpdb;
			$q = &$wp_query;
			$qv = &$q->query_vars;

			if (empty($qv['hijri_archive']) || !empty($qv['m']))
			{
				return $where;
			}
			$new_where = '';
			$hy = $hm = $hd = '';

			if (!empty($qv['hyear']))
				$hy = (int) $qv['hyear'];
			if (!empty($qv['hmonth']))
				$hm = (int) $qv['hmonth'];
			if (!empty($qv['hday']))
				$hd = (int) $qv['hday'];

			$gr_date2 = array();
			if (!empty($hd) && !empty($hm)) {
				$gr_date = $this->hcal->HijriToGregorian($hy, $hm, $hd);
				$date_parameters = array(array('year' => $gr_date['y'], 'monthnum' => $gr_date['m'], 'day' => $gr_date['d']));
			} else {
				if (!empty($hm)) {
					$gr_date = $this->hcal->HijriToGregorian($hy, $hm, 1);
					$gr_date2 = $this->hcal->HijriToGregorian($hy, $hm + 1, 0);
				} else {
					$gr_date = $this->hcal->HijriToGregorian($hy, 1, 1);
					$gr_date2 = $this->hcal->HijriToGregorian($hy + 1, 1, 0);
				}

				$date_parameters = array(array('after' => array('year' => $gr_date['y'], 'month' => $gr_date['m'], 'day' => $gr_date['d']), 'before' => array('year' => $gr_date2['y'], 'month' => $gr_date2['m'], 'day' => $gr_date2['d']), 'inclusive' => TRUE));
			}
			if ($date_parameters) {
				$date_query = new WP_Date_Query($date_parameters);
				$new_where .= $date_query->get_sql();
			}

			$where .= $new_where;
			if (is_month() or is_day()) {
				$q->set('monthnum', $gr_date['m']);
			}
			$q->set('year', $gr_date['y']);

			return $where;
		}

		function posts_results_fl($posts, $wp_query)
		{
			if (!empty($posts)) {
				if (empty($wp_query->query_vars['year'])) {
					$dt = new DateTime($posts[0]->post_date);
					list($gy, $gm) = explode(' ', $dt->format('Y n'));
					$wp_query->set('monthnum', $gm);
					$wp_query->set('year', $gy);
				}
				if (empty($wp_query->query_vars['hyear'])) {
					$dt = new \hijri\DateTime($posts[0]->post_date);
					list($hy, $hm) = explode(' ', $dt->format('_Y _n'));
					$wp_query->set('hmonth', $hm);
					$wp_query->set('hyear', $hy);
				}
			}
			return $posts;
		}

		function hijri_pre_post_link_fl($permalink, $post)
		{
			if ((strpos($permalink, '%year%') === FALSE) && (strpos($permalink, '%monthnum%') === FALSE) && (strpos($permalink, '%day%') === FALSE)) {
				return $permalink;
			}
			$rewritecode = array('%year%', '%monthnum%', '%day%');
			$dt = new \hijri\datetime($post->post_date, Null, 'en', $this->hcal);
			$rewritereplace = explode(' ', $dt->format('_Y _m _d'));
			return str_replace($rewritecode, $rewritereplace, $permalink);
		}

		/**
		 * if query vars are Hijri assgin them to new vars, set hijri_archive=1
		 *
		 * @param unknown $query
		 */
		function pre_get_posts_action($query)
		{
			global $wpdb;
			$q = $query->query;
			$hy = $hm = $hd = '';
			// processing m var
			if (!empty($q['m'])) {
				$hy = (int) (substr($q['m'], 0, 4));
				if ($hy < 1700) {
					$query->set('hijri_archive', '1');
					$query->set('hyear', $hy);
					$len = strlen($q['m']);
					if ($len > 5) {
						$hm = intval(substr($q['m'], 4, 2));
						$query->set('hmonth', $hm);
						if ($len > 7) {
							$hd = intval(substr($q['m'], 6, 2));
							$query->set('hday', $hd);
							$gr_date = $this->hcal->HijriToGregorian($hy, $hm, $hd);
							$query->set('m', zeroise($gr_date['y'], 4) . zeroise($gr_date['m'], 2) . zeroise($gr_date['d'], 2) . (($len > 8) ? substr($q['m'], 8) : ''));
							return;
						}
					}
					$query->set('m', '');
					return;
				}
			}
			// processing other date vars
			if (!empty($q['year'])) {
				$hy = (int) $q['year'];
				if ($hy < 1701) {
					$query->set('hyear', $hy);
					$query->set('year', '');
					$query->set('hijri_archive', '1');
					if (!empty($q['monthnum'])) {
						$hm = (int) $q['monthnum'];
						$query->set('hmonth', $hm);
						$query->set('monthnum', '');
					}
					if (!empty($q['day'])) {
						$hd = (int) $q['day'];
						$query->set('hday', $hd);
						$query->set('day', '');
					}
				}
			}
		}

		/**
		 * handling date_i18n redirect to hijri_date_i18n if needed
		 *
		 * @param string $j
		 * @param string $req_format
		 * @param unknown $i
		 * @param unknown $gmt
		 * @return unknown|string
		 */
		function handle_date_i18n($j, $req_format, $i, $gmt)
		{
			global $wp_locale;
			if (strpos($req_format, '_') !== FALSE) {
				return $this->hijri_date_i18n($req_format, $i, $gmt);
			} elseif ($this->settings['force_hijri']) {
				$new_req_format = $this->convert_format_to_hijri($req_format);
				if ($new_req_format != $req_format) {
					return $this->hijri_date_i18n($new_req_format, $i, $gmt);
				}
			}
			if ($wp_locale->is_rtl()) {
				$j = strtr($j, array('AD' => 'م', 'AH' => 'هـ'));
			}

			return $j;
		}

		/**
		 * handling wp_date redirect to wp_hijri_date if needed
		 *
		 * @since 1.5.0
		 *
		 * @param string       $date      Formatted date string.
		 * @param string       $format    Format to display the date.
		 * @param int          $timestamp Unix timestamp.
		 * @param DateTimeZone $timezone  Timezone.
		 * @return string
		 */
		function handle_wp_date($date, $format, $timestamp, $timezone)
		{
			global $wp_locale;

			if (strpos($format, '_') !== FALSE) {
				return $this->wp_hijri_date($format, $timestamp, $timezone);
			} elseif ($this->settings['force_hijri']) {
				$new_format = $this->convert_format_to_hijri($format);
				if ($new_format != $format) {
					return $this->wp_hijri_date($new_format, $timestamp, $timezone);
				}
			}

			if ($wp_locale->is_rtl()) {
				$date = strtr($date, array('AD' => 'م', 'AH' => 'هـ'));
			}

			return $date;
		}
		/**
		 * Hijri version of WP date_i18n
		 *
		 * @param string $format
		 * @param mixed $unixtimestamp
		 * @param mixed $gmt
		 * @return string
		 */
		function hijri_date_i18n($format, $unixtimestamp = false, $gmt = false)
		{
			global $wp_locale;
			$i = $unixtimestamp;
			if (false === $i) {
				if (!$gmt)
					$i = current_time('timestamp');
				else
					$i = time();
					// we should not let date() interfere with our
					// specially computed timestamp
				$gmt = true;
			}
			$datefunc = $gmt ? 'gmdate' : 'date';
			list($gy, $gm, $gd, $w, $mn, $am, $AM) = explode('/', $datefunc('Y/m/d/w/n/a/A', $i));

			if (strpos($format, '_') !== FALSE) {
				$jd = gregoriantojd($gm, $gd, $gy);
				if (empty($this->hcal)) {
					$this->hcal = new hijri\Calendar($this->settings);
				}
				$this->hcal->jd2hijri($jd, $hy, $hm, $hd, $z);
				$hsmonths = array(1 => 'Muh', 'Saf', 'Rb1', 'Rb2', 'Jm1', 'Jm2', 'Raj', 'Sha', 'Ram', 'Shw', 'Qid', 'Hij');
			}

			// Begin of formating
			$str = '';
			$c = str_split($format);
			$count_c = count($c);
			for ($n = 0; $n < $count_c; $n++) {

				if ($c[$n] == '\\') {
					if ($n < ($count_c-1)) {
						$str .= '\\' . $c[++$n];
					}
				} elseif ($c[$n] == '_') {
					$n++;
					if (!($n < $count_c))
						break;

					switch ($c[$n])
					{
						case 'j' :
							$str .= $hd;
							if (($n + 1) < $count_c  && $c[$n + 1] == 'S') {
								if ($this->lang == 'en') {
									$str .= backslashit(\hijri\Calendar::english_suffix($hd));
								}
								$n++;
							}
						break;

						case 'd' :
							$str .= str_pad($hd, 2, '0', STR_PAD_LEFT);
							if (($n + 1) < $count_c  && $c[$n + 1] == 'S') {
								if ($this->lang == 'en') {
									$str .= backslashit(\hijri\Calendar::english_suffix($hd));
								}
								$n++;
							}
						break;

						case 'z' :
							$str .= $z - 1;
						break;

						case 'F' :
							$str .= backslashit($this->hcal->month_name($hm, $this->lang));
						break;

						case 'M' :
							if (in_array($this->lang, array('en', 'fr', 'de', 'es', 'pt', 'it', 'en'))) {
								$str .= backslashit($hsmonths[$hm], 'A..z');
							} else {
								$str .= backslashit($this->hcal->month_name($hm, $this->lang), 'A..z');
							}

						break;
						case 't' :
							$str .= $this->hcal->days_in_month($hm, $hy);
						break;

						case 'm' :
							$str .= str_pad($hm, 2, '0', STR_PAD_LEFT);
						break;

						case 'n' :
							$str .= $hm;
						break;

						case 'y' :
							$str .= substr($hy, 2);
						break;

						case 'Y' :
							$str .= $hy;
						break;

						case 'L' :
							$str .= $this->hcal->leap_year($hy);
						break;

						case 'S' :
							if ($this->lang == 'en') {
								$str .= backslashit(\hijri\Calendar::english_suffix($hd));
							}
						break;
						case 'W' :
						case 'o' :
						break;
					}
				} elseif ((!empty($wp_locale->month)) && (!empty($wp_locale->weekday))) {
					switch ($c[$n])
					{
						case 'l' :
							$str .= backslashit($wp_locale->get_weekday($w));
						break;
						case 'D' :
							$str .= backslashit($wp_locale->get_weekday_abbrev($wp_locale->get_weekday($w)));
						break;

						case 'F' :
							$str .= backslashit($wp_locale->get_month($mn));
						break;

						case 'M' :
							$str .= backslashit($wp_locale->get_month_abbrev($wp_locale->get_month($mn)));
						break;

						case 'a' :
							$str .= backslashit($wp_locale->get_meridiem($am));
						break;

						case 'A' :
							$str .= backslashit($wp_locale->get_meridiem($AM));
						break;

						case 'S' : // not used in Non English
							if ($this->lang == 'en') {
								$str .= 'S';
							}
						break;
						case 'P' :
						case 'I' :
						case 'O' :
						case 'T' :
						case 'Z' :
						case 'e' :
							$timezone_string = get_option('timezone_string');
							if ($timezone_string) {
								$timezone_object = timezone_open($timezone_string);
								$date_object = date_create(null, $timezone_object);
								$str .= date_format($date_object, $timezone_format);
							}
						break;
						default :
							$str .= $c[$n];
						break;
					}
				} else {
					$str .= $c[$n];
				}
			}

			if ($wp_locale->is_rtl()) {
				$str = strtr($str, array('\A\D' => 'م', '\A\H' => 'هـ'));
			}

			return @$datefunc($str, $i);
		}

		/**
		 * Hijri version of WP wp_date
		 *
		* Retrieves the date, in localized format.
		*
		* This is a newer function, intended to replace `date_i18n()` without legacy quirks in it.
		*
		* Note that, unlike `date_i18n()`, this function accepts a true Unix timestamp, not summed
		* with timezone offset.
		*
		* @since 1.5.0
		*
		* @param string       $format    PHP date format.
		* @param int          $timestamp Optional. Unix timestamp. Defaults to current time.
		* @param DateTimeZone $timezone  Optional. Timezone to output result in. Defaults to timezone
		*                                from site settings.
		* @return string|false The date, translated if locale specifies it. False on invalid timestamp input.
		*/
		function wp_hijri_date($format, $timestamp = null, $timezone = null)
		{
			if ( null === $timestamp ) {
				$timestamp = time();
			} elseif ( ! is_numeric( $timestamp ) ) {
				return false;
			}

			if ( ! $timezone ) {
				$timezone = wp_timezone();
			}

			$datetime = date_create( '@' . $timestamp );
			$datetime->setTimezone( $timezone );

			return $this->wp_hijri_format($datetime, $format);
		}

		/**
		 * Hijri format
		 *
		* Retrieves the date, in localized format.
		*
		* @since 1.5.0
		*
		* @param DateTime     $datetime  PHP datetime object.
		* @param string        $format Valid date format.
		* @return string 	The date, translated if locale specifies it, with Hijri Support
		*/
		function wp_hijri_format($datetime, $format)
		{
			global $wp_locale;

			if ((!empty($wp_locale->month)) && (!empty($wp_locale->weekday)) || (strpos($format, '_') !== FALSE) ) {
				$format = preg_replace( '/(?<!\\\\)r/', DATE_RFC2822, $format );

				list($gy, $gm, $gd, $w, $mn, $am, $AM) = explode('/', $datetime->format('Y/m/d/w/n/a/A'));

				if (strpos($format, '_') !== FALSE) {
					$jd = gregoriantojd($gm, $gd, $gy);
					if (empty($this->hcal)) {
						$this->hcal = new hijri\Calendar($this->settings);
					}
					$this->hcal->jd2hijri($jd, $hy, $hm, $hd, $z);
					$hsmonths = array(1 => 'Muh', 'Saf', 'Rb1', 'Rb2', 'Jm1', 'Jm2', 'Raj', 'Sha', 'Ram', 'Shw', 'Qid', 'Hij');
				}

				// Begin of formating
				$str = '';
				$c = str_split($format);
				$count_c = count($c);
				for ($n = 0; $n < $count_c; $n++) {

					if ($c[$n] == '\\') {
						if ($n < $count_c-1) {
							$str .= '\\' . $c[++$n];
						}
					} elseif ($c[$n] == '_') {
						$n++;
						if (!($n < $count_c))
							break;

						switch ($c[$n])
						{
							case 'j' :
								$str .= $hd;
								if (($n + 1) < $count_c  && $c[$n + 1] == 'S') {
									if ($this->lang == 'en') {
										$str .= backslashit(\hijri\Calendar::english_suffix($hd));
									}
									$n++;
								}
							break;

							case 'd' :
								$str .= str_pad($hd, 2, '0', STR_PAD_LEFT);
								if (($n + 1) < $count_c  && $c[$n + 1] == 'S') {
									if ($this->lang == 'en') {
										$str .= backslashit(\hijri\Calendar::english_suffix($hd));
									}
									$n++;
								}
							break;

							case 'z' :
								$str .= $z - 1;
							break;

							case 'F' :
								$str .= backslashit($this->hcal->month_name($hm, $this->lang));
							break;

							case 'M' :
								if (in_array($this->lang, array('en', 'fr', 'de', 'es', 'pt', 'it', 'en'))) {
									$str .= backslashit($hsmonths[$hm], 'A..z');
								} else {
									$str .= backslashit($this->hcal->month_name($hm, $this->lang), 'A..z');
								}

							break;
							case 't' :
								$str .= $this->hcal->days_in_month($hm, $hy);
							break;

							case 'm' :
								$str .= str_pad($hm, 2, '0', STR_PAD_LEFT);
							break;

							case 'n' :
								$str .= $hm;
							break;

							case 'y' :
								$str .= substr($hy, 2);
							break;

							case 'Y' :
								$str .= $hy;
							break;

							case 'L' :
								$str .= $this->hcal->leap_year($hy);
							break;

							case 'S' :
								if ($this->lang == 'en') {
									$str .= backslashit(\hijri\Calendar::english_suffix($hd));
								}
							break;
							case 'W' :
							case 'o' :
							break;
						}
					} elseif ((!empty($wp_locale->month)) && (!empty($wp_locale->weekday))) {
						switch ($c[$n])
						{
							case 'l' :
								$str .= backslashit($wp_locale->get_weekday($w));
							break;
							case 'D' :
								$str .= backslashit($wp_locale->get_weekday_abbrev($wp_locale->get_weekday($w)));
							break;

							case 'F' :
								$str .= backslashit($wp_locale->get_month($mn));
							break;

							case 'M' :
								$str .= backslashit($wp_locale->get_month_abbrev($wp_locale->get_month($mn)));
							break;

							case 'a' :
								$str .= backslashit($wp_locale->get_meridiem($am));
							break;

							case 'A' :
								$str .= backslashit($wp_locale->get_meridiem($AM));
							break;

							case 'S' : // not used in Non English
								if ($this->lang == 'en') {
									$str .= 'S';
								}
							break;

							default :
								$str .= $c[$n];
							break;
						}
					} else {
						$str .= $c[$n];
					}
				}
			} else {
				$str = $format;
			}
			if (is_rtl()) {
				$str = strtr($str, array('\A\D' => 'م', '\A\H' => 'هـ','AD' => 'م', 'AH' => 'هـ'));
			}
			$str = $datetime->format( $str );
			return $str;
		}

		function handle_date_formats($formats)
		{
			return array_merge($formats, array('D _j _F _Y\A\H j-n-Y\A\D', '_j-_n-_Y\A\H j-n-Y\A\D', 'D _j _F _Y\A\H', '_j-_n-_Y'));
		}

		private function convert_format_to_hijri($format, $add_AH = FALSE)
		{
			if (strpos($format, '_') === FALSE) {
				$format = ' ' . $format;
				$format = preg_replace('/([^\\_])([jdzFMtmnyYL])/', '\1_\2', $format);
				$format = preg_replace('/,([^ ])/', ', \1', $format);
				$format = str_replace(array('_y\A\D', '_Y\A\D'), array('_y\A\H', '_Y\A\H'), $format);
				if ($add_AH && strpos($format, '\A\H') === FALSE)
					$format = str_replace(array('_y', '_Y'), array('_y\A\H', '_Y\A\H'), $format);
				$format = substr($format, 1);
			}
			return $format;
		}

		private function _update_options($thesettings)
		{
			if ( is_multisite() ) {
				update_site_option('Hijri_Settings', json_encode($thesettings));
			} else {
				update_option( 'Hijri_Settings', json_encode($thesettings) );
			}

		}

		function admin_save_options()
		{
			if (isset($_POST['action']) && check_admin_referer('hijri_month_adj') == 1) {
				$myhijri_settings = array('adj_data' => $this->settings['adj_data'], 'grdate_format' => 'U');

				$adj = new hijri\CalendarAdjustment($myhijri_settings);
				if ($_POST['action'] == 'update') {
					$this->settings['umalqura'] = ($_POST['Hijri_Umalqura'] == 'True') ? TRUE : FALSE;
					$this->settings['langcode'] = $_POST['Hijri_lang'];
					$this->lang = $_POST['Hijri_lang'];
					if (empty($this->lang)) {
						$this->set_lang();
					}
					$this->settings['hijri_url'] = !empty($_POST['hijri_url']);
					$this->settings['force_hijri'] = !empty($_POST['force_hijri']);
					update_option('date_format', stripcslashes($_POST['date_format']));
					$this->_update_options($this->settings);
					add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
				} elseif ($_POST['action'] == 'modify') {
					$month = $_POST['month'];
					$year = $_POST['year'];
					$month_start = $_POST['month_start'];
					if ($adj->add_adj($month, $year, $month_start) == TRUE) {

						$this->settings['adj_data'] = $adj->get_adjdata(FALSE);
						$this->_update_options($this->settings);
						add_settings_error('general', 'settings_updated', sprintf(__('The adjustment of month %s from year %d saved successfully', 'wp-hijri'), __($adj->month_name($month, 'en'), 'wp-hijri'), $year), 'updated');
					} else {
						add_settings_error('general', 'settings_errors', __('Some errors'), 'error');
					}
				} elseif ($_POST['action'] == 'delete') {
					$month = $_POST['month'];
					$year = $_POST['year'];
					$adj->del_adj($month, $year);
					$this->settings['adj_data'] = $adj->get_adjdata(FALSE);
					$this->_update_options($this->settings);
					add_settings_error('general', 'settings_updated', sprintf(__('The adjustment of month %s from year %d deleted successfully', 'wp-hijri'), __($adj->month_name($month, 'en'), 'wp-hijri'), $year), 'updated');
				}
			}
		}
	}
}

if (!function_exists('wp_timezone'))
{
	function wp_timezone()
	{
		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
		return new DateTimeZone($timezone_string);
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return new DateTimeZone($tz_offset);
	}
}


if (class_exists('WP_Hijri')) {
	register_activation_hook(__FILE__, array('WP_Hijri', 'activate'));
	register_deactivation_hook(__FILE__, array('WP_Hijri', 'deactivate'));
	$hijri = new WP_Hijri();
}
