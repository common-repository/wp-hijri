<?php
include_once THE_WP_HIJRI_PATH . 'include/hijri.class.php';
add_action('widgets_init', 'hijri_widgets');

function hijri_widgets() {
	register_widget('Widget_Hijri_Archives');
	register_widget('Widget_Hijri_Calendar');
}

class Widget_Hijri_Archives extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_archive', 'description' => __('A Hijri monthly archive of your site&#8217;s Posts.', 'wp-hijri'));
		parent::__construct('hijri_archives', __('Hijri Archives', 'wp-hijri'), $widget_ops);
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		$c = !empty($instance['count']) ? '1' : '0';
		$d = !empty($instance['dropdown']) ? '1' : '0';

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Archives') : $instance['title'], $instance, $this->id_base);

		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ($d) {
			$dropdown_id = "{$this->id_base}-dropdown-{$this->number}";
			?>
			<label class="screen-reader-text" for="<?php echo esc_attr($dropdown_id); ?>"><?php echo $title; ?></label>
			<select id="<?php echo esc_attr($dropdown_id); ?>" name="archive-dropdown" onchange='document.location.href = this.options[this.selectedIndex].value;'>
				<?php
				/**
				 * Filter the arguments for the Archives widget drop-down.
				 *
				 * @since 2.8.0
				 *
				 * @see get_hijri_archives()
				 *
				 * @param array $args An array of Archives widget drop-down arguments.
				 */
				$dropdown_args = apply_filters('widget_archives_dropdown_args', array(
					'type' => 'monthly',
					'format' => 'option',
					'show_post_count' => $c
				));

				switch ($dropdown_args['type']) {
					case 'yearly':
						$label = __('Select Year');
						break;
					case 'monthly':
						$label = __('Select Month');
						break;
					case 'daily':
						$label = __('Select Day');
						break;
				}
				?>

				<option value=""><?php echo esc_attr($label); ?></option>
				<?php get_hijri_archives($dropdown_args); ?>

			</select>
			<?php
		} else {
			?>
			<ul>
				<?php
				/**
				 * Filter the arguments for the Archives widget.
				 *
				 * @since 2.8.0
				 *
				 * @see get_hijri_archives()
				 *
				 * @param array $args An array of Archives option arguments.
				 */
				get_hijri_archives(apply_filters('widget_archives_args', array(
					'type' => 'monthly',
					'show_post_count' => $c
				)));
				?>
			</ul>
			<?php
		}

		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array('title' => '', 'count' => 0, 'dropdown' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		return $instance;
	}

	/**
	 * @param array $instance
	 */
	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => '', 'count' => 0, 'dropdown' => ''));
		$title = strip_tags($instance['title']);
		$count = $instance['count'] ? 'checked="checked"' : '';
		$dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label>
			<br/>
			<input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
		</p>
		<?php
	}

}

class Widget_Hijri_Calendar extends WP_Widget {

	public function __construct() {
		parent::__construct('hijri_calendar', __('Hijri Calendar', 'wp-hijri'), array('classname' => 'widget_calendar', 'description' => __('You can add the calendar for one month automatic or by customize the number for month and year.', 'wp-hijri')));
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		$title = (empty($instance['title'])) ? '' : $instance['title'];

		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<div id="calendar_wrap">';
		get_hijri_calendar();
		echo '</div>';
		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	/**
	 * @param array $instance
	 */
	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => ''));
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<?php
	}

}

function get_hijri_calendar($initial = true, $echo = true) {
	global $wpdb, $wp_locale, $hijri, $wp_query, $wp_rewrite;
	$hcal = &$hijri->hcal;
	$hm = $hy = '';
	if (!empty($wp_query->query_vars['hyear']) && !empty($wp_query->query_vars['hmonth'])) {
		$hy = (int) $wp_query->query_vars['hyear'];
		$hm = (int) $wp_query->query_vars['hmonth'];
	} elseif (!empty($wp_query->query_vars['year']) && !empty($wp_query->query_vars['monthnum'])) {
		$gy = (int) $wp_query->query_vars['year'];
		$gm = (int) $wp_query->query_vars['monthnum'];

		if (!empty($wp_query->query_vars['day'])) {
			$gd = (int) $wp_query->query_vars['day'];
		} else {
			$gd = $wpdb->get_var("SELECT DAY(post_date) FROM $wpdb->posts
			WHERE YEAR(post_date)=$gy AND MONTH(post_date)=$gm
			AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date LIMIT 1");
			if (empty($gd))
				$gd = 1;
		}
		$hdate = $hcal->GregorianToHijri($gy, $gm, $gd);
		$hy = $hdate['y'];
		$hm = $hdate['m'];
	}

	$hdate = new \hijri\datetime(Null, NULL, $hijri->lang, $hcal);
	list($thd, $thm, $thy) = explode('/', $hdate->format('_j/_n/_Y'));
	if (empty($hy) && empty($hm)) {
		$hy = $thy;
		$hm = $thm;
	}
	$key = md5($hm . $hy);

	if ($cache = wp_cache_get('get_hijri_calendar', 'calendar')) {
		if (is_array($cache) && isset($cache[$key])) {
			if ($echo) {

				echo $cache[$key];
				return;
			} else {
				return $cache[$key];
			}
		}
	}

	if (!is_array($cache))
		$cache = array();


	// week_begins = 0 stands for Sunday
	$week_begins = intval(get_option('start_of_week'));

	$last_day = $hcal->days_in_month($hm, $hy);
	list($start_wd, $gd1, $gm1, $gy1, ) = explode('/', \hijri\datetime::createFromHijri($hy, $hm, 1)->format('w/j/n/Y'));
	list($gd2, $gm2, $gy2) = explode('/', \hijri\datetime::createFromHijri($hy, $hm + 1, 0)->format('j/n/Y'));

	// Get the next and previous month and year with at least one post
	$previous = $wpdb->get_row("SELECT DAY(post_date) AS day, MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < '$gy1-$gm1-$gd1'
		AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date DESC
			LIMIT 1");
	$next = $wpdb->get_row("SELECT DAY(post_date) AS day, MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > '$gy2-$gm2-$gd2 23:59:59'
		AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date ASC
			LIMIT 1");

	$calendar_caption = $hcal->month_name($hm, $hijri->lang) . ' ' . $hy;
	$calendar_output = '<table id="wp-calendar">
	<caption>' . $calendar_caption . '</caption>
	<thead>
	<tr>';

	$myweek = array();

	for ($wdcount = 0; $wdcount <= 6; $wdcount++) {
		$myweek[] = $wp_locale->get_weekday(($wdcount + $week_begins) % 7);
	}

	foreach ($myweek as $wd) {
		$day_name = $initial ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
		$wd = esc_attr($wd);
		$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
	}

	$calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';


	if ($previous) {
		$prehdate = $hcal->GregorianToHijri($previous->year, $previous->month, $previous->day);

		$calendar_output .= "\n\t\t" . '<td colspan="3" id="prev"><a href="' . get_month_link($prehdate['y'], $prehdate['m']) . '">&laquo; ' . $hcal->month_name($prehdate['m'], $hijri->lang, TRUE) . '</a></td>';
	} else {
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="prev" class="pad">&nbsp;</td>';
	}

	$calendar_output .= "\n\t\t" . '<td class="pad">&nbsp;</td>';

	if ($next) {
		$nexhdate = $hcal->GregorianToHijri($next->year, $next->month, $next->day);
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="next"><a href="' . get_month_link($nexhdate['y'], $nexhdate['m']) . '">' . $hcal->month_name($nexhdate['m'], $hijri->lang, TRUE) . ' &raquo;</a></td>';
	} else {
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="next" class="pad">&nbsp;</td>';
	}

	$calendar_output .= '
	  </tr>
	  </tfoot>
	<tbody>
	<tr>';

	$daywithpost = array();

	// Get days with posts


	$dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date) AS d, MONTH(post_date) AS m, YEAR(post_date) AS y
		FROM $wpdb->posts WHERE post_date >= '{$gy1}-{$gm1}-{$gd1} 00:00:00'
		AND post_type = 'post' AND post_status = 'publish'
		AND post_date <= '{$gy2}-{$gm2}-{$gd2} 23:59:59'");
	if ($dayswithposts) {
		foreach ((array) $dayswithposts as $daywith) {
			$dt=new \hijri\datetime($daywith->y . '-' . $daywith->m . '-' . $daywith->d, NULL, NULL, $hcal);
			$daywithpost[] = $dt->format('_j');
		}
	}

	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'camino') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false)
		$ak_title_separator = "\n";
	else
		$ak_title_separator = ', ';

	$ak_titles_for_day = array();
	$ak_post_titles = $wpdb->get_results("SELECT ID, post_title, DAYOFMONTH(post_date) as d , MONTH(post_date) AS m,YEAR(post_date) AS y "
			. "FROM $wpdb->posts "
			. "WHERE post_date >= '{$gy1}-{$gm1}-{$gd1} 00:00:00' "
			. "AND post_date <= '{$gy2}-{$gm2}-{$gd2} 23:59:59' "
			. "AND post_type = 'post' AND post_status = 'publish'"
	);
	if ($ak_post_titles) {
		foreach ((array) $ak_post_titles as $ak_post_title) {

			/** This filter is documented in wp-includes/post-template.php */
			$post_title = esc_attr(apply_filters('the_title', $ak_post_title->post_title, $ak_post_title->ID));
			$dt=new \hijri\datetime($ak_post_title->y . '-' . zeroise($ak_post_title->m, 2) . '-' . zeroise($ak_post_title->d, 2), NULL, NULL, $hcal);
			$hd = $dt->format('_j');
			if (empty($ak_titles_for_day['day_' . $hd]))
				$ak_titles_for_day['day_' . $hd] = '';
			if (empty($ak_titles_for_day["$hd"])) // first one
				$ak_titles_for_day["$hd"] = $post_title;
			else
				$ak_titles_for_day["$hd"] .= $ak_title_separator . $post_title;
		}
	}

	// See how much we should pad in the beginning
	$pad = calendar_week_mod($start_wd - $week_begins);
	if (0 != $pad)
		$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr($pad) . '" class="pad">&nbsp;</td>';

	$daysinmonth = $last_day;
	$wd = $pad;
	for ($day = 1; $day <= $daysinmonth; ++$day) {
		if (isset($newrow) && $newrow)
			$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
		$newrow = false;

		if ($day == $thd) //&& $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')) )
			$calendar_output .= '<td id="today">';
		else
			$calendar_output .= '<td>';

		if (in_array($day, $daywithpost)) { // any posts today?
			$daylink = $wp_rewrite->get_day_permastruct();
			if (!empty($daylink)) {
				$daylink = str_replace('%year%', $hy, $daylink);
				$daylink = str_replace('%monthnum%', zeroise(intval($hm), 2), $daylink);
				$daylink = str_replace('%day%', zeroise(intval($day), 2), $daylink);
				$daylink = home_url(user_trailingslashit($daylink, 'day'));
			} else {
				$daylink = home_url('?m=' . $hy . zeroise($hm, 2) . zeroise($day, 2));
			}

			//list($gd, $gm, $gy) = explode('/', \hijri\datetime::createFromHijri($hy, $hm, $day)->format('j/n/Y'));
			$calendar_output .= '<a href="' . $daylink . '" title="' . esc_attr($ak_titles_for_day[$day]) . "\">$day</a>";
		} else {
			$calendar_output .= $day;
		}
		$calendar_output .= '</td>';

		if (6 == $wd) {
			$newrow = true;
			$wd = 0;
		} else {
			$wd++;
		}
	}

	$pad = 7 - $wd;
	if ($pad != 0 && $pad != 7)
		$calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr($pad) . '">&nbsp;</td>';

	$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

	$cache[$key] = $calendar_output;
	wp_cache_set('get_hijri_calendar', $cache, 'calendar');
	if ($echo) {
		/**
		 * Filter the HTML calendar output.
		 *
		 * @since 3.0.0
		 *
		 * @param string $calendar_output HTML output of the calendar.
		 */
		echo $calendar_output;
	} else {
		/** This filter is documented in wp-includes/general-template.php */
		return $calendar_output;
	}
}

function get_hijri_archives($args = '') {
	global $wpdb, $hijri;
	$hcal = &$hijri->hcal;
	$defaults = array(
		'type' => 'monthly', 'limit' => '',
		'format' => 'html', 'before' => '',
		'after' => '', 'show_post_count' => false,
		'echo' => 1, 'order' => 'DESC',
	);

	$r = wp_parse_args($args, $defaults);

	if ('' == $r['type']) {
		$r['type'] = 'monthly';
	}

	if (!empty($r['limit'])) {
		$r['limit'] = absint($r['limit']);
		$r['limit'] = ' LIMIT ' . $r['limit'];
	}

	$order = strtoupper($r['order']);
	if ($order !== 'ASC') {
		$order = 'DESC';
	}

	// this is what will separate dates on weekly archive links
	$archive_week_separator = '&#8211;';

	// over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
	$archive_date_format_over_ride = 0;

	// options for daily archive (only if you over-ride the general date format)
	$archive_day_date_format = 'Y/m/d';

	// options for weekly archive (only if you over-ride the general date format)
	$archive_week_start_date_format = 'Y/m/d';
	$archive_week_end_date_format = 'Y/m/d';

	if (!$archive_date_format_over_ride) {
		$archive_day_date_format = get_option('date_format');
		$archive_week_start_date_format = get_option('date_format');
		$archive_week_end_date_format = get_option('date_format');
	}

	/**
	 * Filter the SQL WHERE clause for retrieving archives.
	 *
	 * @since 2.2.0
	 *
	 * @param string $sql_where Portion of SQL query containing the WHERE clause.
	 * @param array  $r         An array of default arguments.
	 */
	$where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'", $r);

	/**
	 * Filter the SQL JOIN clause for retrieving archives.
	 *
	 * @since 2.2.0
	 *
	 * @param string $sql_join Portion of SQL query containing JOIN clause.
	 * @param array  $r        An array of default arguments.
	 */
	$join = apply_filters('getarchives_join', '', $r);

	$output = '';

	$last_changed = wp_cache_get('last_changed', 'posts');
	if (!$last_changed) {
		$last_changed = microtime();
		wp_cache_set('last_changed', $last_changed, 'posts');
	}

	$limit = $r['limit'];
	$after = $r['after'];

	if ('monthly' == $r['type']) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAY(post_date) AS `day`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY DATE(post_date) ORDER BY post_date $order $limit";
		$key = md5($query);
		$key = "get_hijri_archives:monthly:$key:$last_changed";
		if (!$archive_arr = wp_cache_get($key, 'posts')) {
			$results = $wpdb->get_results($query);
			$archive_arr = array();
			foreach ((array) $results as $result) {
				$hdate = $hcal->GregorianToHijri($result->year, $result->month, $result->day);
				$month_key = '' . $hdate['y'] . '-' . $hdate['m'];
				if (key_exists($month_key, $archive_arr)) {
					$archive_arr[$month_key]+=$result->posts;
				} else {
					$archive_arr[$month_key] = $result->posts;
				}
			}
			wp_cache_set($key, $archive_arr, 'posts');
		}
		if ($archive_arr) {
			foreach ($archive_arr as $arr_key => $posts) {
				list($year, $month) = explode('-', $arr_key);
				$url = get_month_link($year, $month);
				/* translators: 1: month name, 2: 4-digit year */
				$text = sprintf(__('%1$s %2$d'), $hcal->month_name($month, $hijri->lang), $year);
				if ($r['show_post_count']) {
					$r['after'] = '&nbsp;(' . $posts . ')' . $after;
				}
				$output .= get_archives_link($url, $text, $r['format'], $r['before'], $r['after']);
			}
		}
	} elseif ('yearly' == $r['type']) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAY(post_date) AS `day`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY DATE(post_date) ORDER BY post_date $order $limit";
		$key = md5($query);
		$key = "get_hijri_archives:yearly:$key:$last_changed";

		if (!$archive_arr = wp_cache_get($key, 'posts')) {
			$results = $wpdb->get_results($query);
			$archive_arr = array();
			foreach ((array) $results as $result) {
				$hdate = $hcal->GregorianToHijri($result->year, $result->month, $result->day);
				if (key_exists((int) $hdate['y'], $archive_arr)) {
					$archive_arr[$hdate['y']] += $result->posts;
				} else {
					$archive_arr[$hdate['y']] = $result->posts;
				}
			}
			wp_cache_set($key, $archive_arr, 'posts');
		}
		if ($archive_arr) {
			foreach ($archive_arr as $year => $posts) {
				$url = get_year_link($year);
				$text = sprintf('%d', $year);
				if ($r['show_post_count']) {
					$r['after'] = '&nbsp;(' . $posts . ')' . $after;
				}
				$output .= get_archives_link($url, $text, $r['format'], $r['before'], $r['after']);
			}
		}
	} elseif ('daily' == $r['type']) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5($query);
		$key = "get_hijri_archives:daily:$key:$last_changed";
		if (!$results = wp_cache_get($key, 'posts')) {
			$results = $wpdb->get_results($query);
			wp_cache_set($key, $results, 'posts');
		}
		if ($results) {
			$after = $r['after'];
			foreach ((array) $results as $result) {
				$hdate = $hcal->GregorianToHijri($result->year, $result->month, $result->dayofmonth);
				$url = get_day_link($hdate['y'], $hdate['m'], $hdate['d']);
				$date = sprintf('%1$d-%2$02d-%3$02d 00:00:00', $hdate['y'], $hdate['m'], $hdate['d']);
				$text = mysql2date($archive_day_date_format, $date);
				if ($r['show_post_count']) {
					$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$output .= get_archives_link($url, $text, $r['format'], $r['before'], $r['after']);
			}
		}
	}
	if ($r['echo']) {
		echo $output;
	} else {
		return $output;
	}
}
