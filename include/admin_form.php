<?php
// hijri Months names

$hmonths = array(1 => __("Muharram", 'wp-hijri'),
__("Safar", 'wp-hijri'),
__("Rabi Al Awwal", 'wp-hijri'),
__("Rabi Al Thani", 'wp-hijri'),
__("Jumada Al Oula", 'wp-hijri'),
__("Jumada Al Akhira", 'wp-hijri'),
__("Rajab", 'wp-hijri'),
__("Shaban", 'wp-hijri'),
__("Ramadan", 'wp-hijri'),
__("Shawwal", 'wp-hijri'),
__("Dhul Qidah", 'wp-hijri'),
__("Dhul Hijjah", 'wp-hijri'));

$page_uri = $_SERVER["PHP_SELF"] . '?page=settings_wp_hijri';
$lang = $this->settings['langcode'];
$WPLANG = get_option('WPLANG');
$umalqura = $this->settings['umalqura'];
$Hijri_Date_Format = get_option('date_format');

$myhijri_settings = array(
	'adj_data' => $this->settings['adj_data'],
	'langcode' => $lang,
	'umalqura' => $umalqura,
	'grdate_format' => 'U',
);

$adj = new hijri\CalendarAdjustment($myhijri_settings);

$set_page = 0;


if (isset($_GET['action'])) {
	if ($_GET['action'] == 'modify') {

		$month = sanitize_text_field($_GET['month']);
		$year = sanitize_text_field($_GET['year']);
		if ($month > 0 && $month < 13 && $year >= $adj::umstartyear && $year <= $adj::umendyear) {
			$set_page = 1;
			$header_txt = __("Editing the start of month ", 'wp-hijri') .
			$hmonths[$month] . __(" From Year ", 'wp-hijri') . $year;
			$options_txt = '';
			$start_options = $adj->get_possible_starts($month, $year);

			foreach ($start_options as $start_option) {

				$options_txt.= '<option value="' . $start_option['jd'] . '" ' .
				($start_option['currentset'] ? " selected " : "") . ">" .
				$this->hijri_date_i18n(__('F j, Y', 'wp-hijri'), $start_option['grdate']);
				foreach ($start_option['alsoadjdata'] as $v) {

					$options_txt.= ' ' . sprintf(__('and also will adjust %s from year %d to %s', 'wp-hijri'), $hmonths[$v['month']], $v['year'], $this->hijri_date_i18n(__('F j, Y'), $v['grdate']));
				}
				$options_txt.= "</option>\n";
			}
		}
	} elseif ($_GET['action'] == 'delete') {
		$month = sanitize_text_field($_GET['month']);
		$year = sanitize_text_field($_GET['year']);
		$set_page = 2;
		$del_infos = $adj->auto_del_info($month, $year);
		$del_txt = sprintf(__("Do you want realy to delete the adjustment of month %s of year %d", 'wp-hijri'), $hmonths[$month], $year);
		if (!empty($del_infos)) {
			$del_txt.="<br>" . __("which will delete successively the adjustment of months:", 'wp-hijri');
			foreach ($del_infos as $del_info) {
				//$del_txt.= "<br>" . $hmonths[$del_info['month']] . __(" From Year ", 'wp-hijri') . $del_info['year'];
				$del_txt.= sprintf(__("%s From Year %d", 'wp-hijri'), $hmonths[$del_info['month']], $del_info['year']);
			}
		}
	}
}
if ($set_page == 0) {
	//build OPTIONS_Years
	$option_years = '';
	list($current_year, $current_month, $current_day) = explode('-', $this->hijri_date_i18n('_Y-_n-_j'));
	if ($current_day > 20) {
		$current_month++;
		if ($current_month == 13) {
			$current_year++;
			$current_month = 1;
		}
	}
	for ($n = $adj::umstartyear; $n <= $adj::umendyear; $n++) {
		$selected = ($n == $current_year) ? 'selected' : '';
		$option_years.= "<option value='$n' $selected>$n</option>\n";
	}
	//build OPTIONS_MONTHS
	$option_months = '';
	for ($n = 1; $n < 13; $n++) {
		$selected = ($n == ($current_month)) ? 'selected' : '';
		$option_months.= "<option value='$n' $selected>$n - " . $hmonths[$n] . "</option>\n";
	}
	$default_href = $page_uri . '&amp;action=modify&amp;year=' . $current_year . '&amp;month=' . $current_month;
	//build option_formats
	$date_formats = array(
		"D _j _F _Y\A\H j-n-Y\A\D",
		"D _j-_n-_Y\A\H j-n-Y\A\D",
		"D _j _F _Y\A\H",
		"D j M Y\A\D",
		"D j-n-Y\A\D",
	);
	if (array_search($Hijri_Date_Format, $date_formats) === FALSE) {
		$date_formats[] = $Hijri_Date_Format;
	}
	$option_formats = "";
	foreach ($date_formats as $df) {
		$option_formats.="<option value='$df' " . (($Hijri_Date_Format == $df) ? 'selected ' : '') . '>' .
		$this->hijri_date_i18n($df) . "</option>\n";
	}
}
?>
<script lang="javascript">
function updatehref()
{
	themonth = document.getElementById('month').value;
	theyear = document.getElementById('year').value;
	document.getElementById('add_adj').href = '<?php echo $page_uri ?>' + '&action=modify&year=' + theyear + '&month=' + themonth;
}
</script>
<style type="text/css">
.wp_hijri-item-copy { text-align: center; margin: 3px 0 0 7px; font-size:90%; color: #000000; }
.wp_hijri-item-copy a{  text-decoration:  none; }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<div id="wp_hijri-plugin-options" class="wrap">
	<?php if ($set_page == 0) { ?>
		<form method="post" >
			<input type="hidden" name="action" value="update" />
			<?php wp_nonce_field('hijri_month_adj'); ?>

			<div class="metabox-holder">
				<div class="meta-box-sortables ui-sortable">
					<h1 class="wp_hijri-fonts-hed"><?php _e("Settings WP-Hijri", 'wp-hijri'); ?> |
					<?php echo $this->hijri_date_i18n($Hijri_Date_Format); ?>
					</h1>
						<p class="wp_hijri-fonts-p"><?php _e("You can choose Hijri or Gregorian dates or both to show inside posts and pages.", 'wp-hijri'); ?></p>
						<div class="wp_hijri-table-wrap">
							<table class="widefat wp_hijri-table">
								<tr>
									<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Hijri calendar algorithm", 'wp-hijri'); ?></label></th>
									<td>
										<label><input type="radio" name="Hijri_Umalqura" value="True" <?php
										if ($umalqura) {
											echo 'checked="checked"';
										}
										?> ><?php _e("Um Al Qura algorithm (Recommended)", 'wp-hijri'); ?></label><br>
										<label><input type="radio" name="Hijri_Umalqura" value="False" <?php
										if (!$umalqura) {
											echo 'checked="checked"';
										}
										?>><?php _e("Tabular algorithm", 'wp-hijri'); ?></label><br>
									</td>
								</tr>
								<tr>
									<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Language", 'wp-hijri'); ?></label></th>
									<td>
										<label><input type="radio" name="Hijri_lang" value="" <?php
										if ($lang == '') {
											echo 'checked="checked"';
										}
										?> ><?php _e("WordPress Language", 'wp-hijri'); ?></label><br>
										<label><input type="radio" name="Hijri_lang" value="ar" <?php
										if ($lang == 'ar') {
											echo 'checked="checked"';
										}
										?> ><?php _e("Arabic", 'wp-hijri'); ?></label><br>
										<label><input type="radio" name="Hijri_lang" value="en" <?php
										if ($lang == 'en') {
											echo 'checked="checked"';
										}
										?>><?php _e("English", 'wp-hijri'); ?></label><br>
									</td>
								</tr>
								<tr><th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Other Options", 'wp-hijri'); ?></label></th>
									<td>
										<label for="force_hijri">
											<input type="checkbox" id="force_hijri" name="force_hijri" value="1" <?php checked($this->settings['force_hijri'], TRUE); ?> />
											<?php _e('Force all dates to return Hijri dates.', 'wp-hijri'); ?>
										</label><br/>
										<label for="hijri_url">
											<input type="checkbox" id="hijri_url" name="hijri_url" value="1" <?php checked($this->settings['hijri_url'], TRUE); ?> />
											<?php _e('Use Hijri Date in URLs.', 'wp-hijri'); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Date Format", 'wp-hijri'); ?></label></th>
									<td>
										<select name="Date_Format" onchange="if (this.value == 'custom') {
											$('.show_field').toggle('slide');
										} else {
											if ($('.show_field').css('display') != 'none')
											{
												$('.show_field').toggle('slide');
											}
											document.getElementById('date_format').value = this.value;
										}">
										<?php echo $option_formats ?>
										<option value="custom" id="custom_format_field"><?php _e("Custom Format", 'wp-hijri'); ?></option>
									</select>
									<script>
									$(document).ready(function () {
										$('#show_custom_format').click(function () {
											$('.custom_format').toggle("slide");
										});
									});
									</script>
									<span class="show_field" style="display:none;">
										<input type="text" id="date_format" name="date_format" value="<?php echo $Hijri_Date_Format ?>"> | <a  id="show_custom_format" ><?php _e("Show custom format", 'wp-hijri'); ?></a>
									</span>
								</td>
							</tr>
							<tr class="custom_format" style="display: none;">
								<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("The Custom Format", 'wp-hijri'); ?></label></th>
								<td>
									<ul>
										<span style="color:blue"> <?php _e("Hijri Date Format", 'wp-hijri'); ?> </span>
										<li><code>l , D</code> <?php _e("Display name of day.", 'wp-hijri'); ?></li>
										<li><code>_j</code> <?php _e("Display number day [1-30]", 'wp-hijri'); ?></li>
										<li><code>_d</code> <?php _e("Display number day [01-30]", 'wp-hijri'); ?></li>
										<li><code>_M , _F</code> <?php _e("Display name of the month", 'wp-hijri'); ?></li>
										<li><code>_m</code> <?php _e("Display month as numbers [01-12]", 'wp-hijri'); ?></li>
										<li><code>_n</code> <?php _e("Display month as numbers [1-12]", 'wp-hijri'); ?></li>
										<li><code>_y</code> <?php _e("Display the short numbers for year [36]", 'wp-hijri'); ?></li>
										<li><code>_Y</code> <?php _e("Display the full numbers for year [1436]", 'wp-hijri'); ?></li>
										<span style="color:blue"> <?php _e("Gregorian Date Format", 'wp-hijri'); ?> </span>
										<li><code>l , D</code> <?php _e("Display name of day.", 'wp-hijri'); ?></li>
										<li><code>j</code> <?php _e("Display number day [1-30]", 'wp-hijri'); ?></li>
										<li><code>d</code> <?php _e("Display number day [01-30]", 'wp-hijri'); ?></li>
										<li><code>F</code> <?php _e("Display full names of the months ", 'wp-hijri'); ?></li>
										<li><code>M</code> <?php _e("Display short names of the months", 'wp-hijri'); ?></li>
										<li><code>m</code> <?php _e("Display month as numbers [01-12]", 'wp-hijri'); ?></li>
										<li><code>n</code> <?php _e("Display month as numbers [1-12]", 'wp-hijri'); ?></li>
										<li><code>y</code> <?php _e("Display the short numbers for year [15]", 'wp-hijri'); ?></li>
										<li><code>Y</code> <?php _e("Display the full numbers for year [2015]", 'wp-hijri'); ?></li>
									</ul>
								</td>
							</tr>
						</table>
						<br>
						<input name="submit" id="submit" class="button button-primary" value="<?php _e("Save", 'wp-hijri'); ?>" type="submit">
					</div>
				</form>
				<hr style="border: 2px #e6e6e9 dashed;">
				<h1 class="wp_hijri-fonts-hed"><?php _e("Hijri calendar adjustments", 'wp-hijri'); ?></h1>
				<p class="wp_hijri-fonts-p"><?php _e("Here you can modify the beginning of Hijri months based on Um Al Qura algorithm", 'wp-hijri'); ?></p>
				<?php if (!$umalqura && !empty($this->settings['adj_data'])) {
					?>
					<p style="color:red" class="wp_hijri-fonts-p"><?php _e("Warning: To apply adjustments, Please Choose Um Al-Qura algorithm.", 'wp-hijri'); ?></p>
					<?php
				}
				?>
				<div class="wp_hijri-table-wrap">
					<table class="widefat wp_hijri-table">
						<tr>
							<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Year", 'wp-hijri'); ?></label></th>
							<td>
								<select name="year" id="year" onchange="updatehref()"><?php echo $option_years; ?></select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Month", 'wp-hijri'); ?></label></th>
							<td>
								<select name="month" id="month" onchange="updatehref()"><?php echo $option_months; ?></select>
							</td>
							<td>
								<a name="add_adj" class="button button-primary" id="add_adj" href="<?php echo $default_href; ?>"><?php _e("Add new adjustment", 'wp-hijri'); ?></a>
							</td>
						</tr>

					</table>
					<hr style="border: 2px #e6e6e9 dashed;">

					<h1 class="wp_hijri-fonts-hed"><?php _e("Current Hijri month Adjustments", 'wp-hijri'); ?></h1>
					<div class="wp_hijri-table-wrap">
						<table cellspacing="0" class="wp-list-table widefat fixed striped posts" style="width: 100%">
							<?php
							$current_adjs = $adj->get_current_adjs();
							if (empty($current_adjs)) {
								echo "<tr><td>" . __("There is not any current adjustments", 'wp-hijri') . "</td></tr>";
							} else {
								echo "
								<tr>
								<th>" . __("Year/Month", 'wp-hijri') . "</th>
								<th>" . __("Current Start", 'wp-hijri') . "</th>
								<th>" . __("Default Start", 'wp-hijri') . "</th>
								<th>" . __("Actions", 'wp-hijri') . "</th>
								</tr>";

								foreach ($current_adjs as $adj) {
									echo "<tr><td>" . $hmonths[$adj['month']] . " - " . $adj['month'] . " / " . $adj['year'] . "</td>
									<td>" . $this->hijri_date_i18n(__('F j, Y', 'wp-hijri'), $adj['current']) . "</td>
									<td>" . $this->hijri_date_i18n(__('F j, Y', 'wp-hijri'), $adj['default']) . "</td>
									<td>"
									. "<a class='button button-primary'  href='" . $page_uri . "&action=modify&month=" . $adj['month'] . "&year=" . $adj['year'] . "'>" .
									__("Edit", 'wp-hijri') . "</a> " .
									"<a class='button button-primary' href='" . $page_uri . "&action=delete&month=" . $adj['month'] . "&year=" . $adj['year'] . "'>" .
									__("Delete", 'wp-hijri') . "</a>" .
									"</td>
									</tr>";
								}
							}
							?>
						</table>
					</div>
				</div>
				<div class="wp_hijri-item-copy"><?php _e("Develop : <a target='_blank' href='https://twitter.com/hubaishan'>Saeed Hubaishan</a> & <a target='_blank' href='https://twitter.com/mohdokfie'>Mohammad Okfie</a>, By using 'Hijri Date lib' Library.", 'wp-hijri'); ?><br>
				</div>

			</div>

			<?php
		} elseif ($set_page == 1) {
			?>
			<h1 class="wp_hijri-fonts-hed"><?php echo $header_txt ?></h1>
			<div class="wp_hijri-table-wrap">
				<form method="post" action=<?php echo $page_uri ?>>
					<?php wp_nonce_field('hijri_month_adj'); ?>
					<table class="widefat wp_hijri-table">
						<tr>
							<th scope="row"><label class="description wp_hijri-fonts-p"><?php _e("Possible starts", 'wp-hijri'); ?></label></th>
							<td>

								<select name="month_start" id="month_start" ><?php echo $options_txt; ?></select>
								<input type="hidden" id="month" name="month" value="<?php echo $month; ?>" />
								<input type="hidden" id="year" name="year" value="<?php echo $year; ?>" />
								<input type="hidden" id="action" name="action" value="modify" />

							</td>
						</tr>
					</table>
					<br>
					<input name="submit" id="submit" class="button button-primary" value="<?php _e("Save Data", 'wp-hijri'); ?>" type="submit">
					<a href="<?php echo $page_uri ?>" class="button button-primary" ><?php echo _e("Cancel", 'wp-hijri') ?></a>
				</from>
			</div>
			<?php
		} elseif ($set_page == 2) {
			?>
			<h1 class="wp_hijri-fonts-hed"><?php echo _e("Delete Confirm", 'wp-hijri') ?></h1>
			<div class="wp_hijri-table-wrap">
				<form method="post" action=<?php echo $page_uri ?>>
					<?php wp_nonce_field('hijri_month_adj'); ?>
					<p><?php echo $del_txt ?></p>
					<input type="hidden" id="month" name="month" value="<?php echo $month; ?>" />
					<input type="hidden" id="year" name="year" value="<?php echo $year; ?>" />
					<input type="hidden" id="action" name="action" value="delete" />

					<input name="submit" id="submit" class="button button-primary" value="<?php _e("OK", 'wp-hijri'); ?>" type="submit">
					<a href="<?php echo $page_uri ?>" class="button button-primary" ><?php echo _e("Cancel", 'wp-hijri') ?></a>
				</from>
			</div>
			<?php
		}
		?>
