<?php


$explore_styles = <<<EOF
<style type="text/css">
.all_links, .explore_title, table.grid {
	font: 14px/18px 'Helvetica Neue', Helvetica, Arial, Sans-serif;
}

.explore_title {
	font-size: 24px;
	font-weight: bold;
}

table.grid {
	border-collapse: collapse;
	border-bottom: 1px solid #ddd;
}

table.grid td {
	padding: 3px 15px 3px 7px;
	text-shadow: #fff 0 1px 0;
	background: #feffff;
	background: -moz-linear-gradient(top, #feffff 0%, #f5f5f5 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #feffff), color-stop(100%, #f5f5f5));
	background: -webkit-linear-gradient(top, #feffff 0%, #f5f5f5 100%);
	background: linear-gradient(top, #feffff 0%, #f5f5f5 100%);
	border-top: 1px solid #ddd;
	border-left: 1px solid #eee;
}

table.grid td  > table.grid,
.explore_show_link,
.explore_hide_link {
	margin: -4px -15px -4px -8px;
}

table.grid tr.head td {
	color: #fff;
	text-shadow: #000000 0 -1px 0;
	background: #b5bdc8;
	background: -moz-linear-gradient(top, #b5bdc8 0%, #828c95 36%, #28343b 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #b5bdc8), color-stop(36%, #828c95), color-stop(100%, #28343b));
	background: -webkit-linear-gradient(top, #b5bdc8 0%, #828c95 36%, #28343b 100%);
	background: linear-gradient(top, #b5bdc8 0%, #828c95 36%, #28343b 100%);
	border: none;
	border-right: 1px solid rgba(255, 255, 255, 0.2);
	white-space: nowrap;
}

table.grid tr.head td:first-child {
	border-top-left-radius: 10px;
}

table.grid tr.head td:last-child {
	border-top-right-radius: 10px;
}

table.grid td.label,
table.grid td.type {
	background: #dddddd;
	background: -moz-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #dddddd), color-stop(100%, #afafaf));
	background: -webkit-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: -o-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: -ms-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: linear-gradient(top, #dddddd 0%, #afafaf 100%);
}

.details_table {
	display: none;
}

table.grid td.label {
	font-weight: bold;
}

.explore_show_link,
.explore_hide_link {
	display: block;
	color: #333;
	padding: 3px 5px;
	text-decoration: none;
}

.explore_show_link {
	background: rgb(255,255,224); /* Old browsers */
	background: -moz-linear-gradient(top, rgba(255,255,224,1) 0%, rgba(255,255,136,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,224,1)), color-stop(100%,rgba(255,255,136,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* IE10+ */
	background: linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* W3C */
}

.explore_hide_link {
	background: rgb(222,239,255); /* Old browsers */
	background: -moz-linear-gradient(top, rgba(222,239,255,1) 0%, rgba(152,190,222,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(222,239,255,1)), color-stop(100%,rgba(152,190,222,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* IE10+ */
	background: linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* W3C */
	margin-bottom: 4px;
}

.all_links {
	color: #333;
}
</style>
EOF;

$explore_scripts = <<<EOF
<script type="text/javascript">
function explore_showDetails(output_id){
	document.getElementById(output_id).style.display = 'block';
	document.getElementById('link_hide_' + output_id).style.display = 'block';
	document.getElementById('link_show_' + output_id).style.display = 'none';
}

function explore_hideDetails(output_id){
	document.getElementById(output_id).style.display = 'none';
	document.getElementById('link_hide_' + output_id).style.display = 'none';
	document.getElementById('link_show_' + output_id).style.display = 'block';
}

function explore_showAll(){
	show_links = document.getElementsByClassName('explore_show_link');
	hide_links = document.getElementsByClassName('explore_hide_link');
	tables = document.getElementsByClassName('details_table');

	for (var i = 0; i < tables.length; i++) tables[i].style.display = 'block';
	for (var i = 0; i < show_links.length; i++) show_links[i].style.display = 'none';
	for (var i = 0; i < hide_links.length; i++) hide_links[i].style.display = 'block';
}

function explore_hideAll(){
	show_links = document.getElementsByClassName('explore_show_link');
	hide_links = document.getElementsByClassName('explore_hide_link');
	tables = document.getElementsByClassName('details_table');

	for (var i = 0; i < tables.length; i++) tables[i].style.display = 'none';
	for (var i = 0; i < show_links.length; i++) show_links[i].style.display = 'block';
	for (var i = 0; i < hide_links.length; i++) hide_links[i].style.display = 'none';
}
</script>
EOF;


function explore($var, $_options = array()){
	
	// Defaults
	$explore_default_options = array(
		'show_type'  => TRUE,
		'enable_js'  => TRUE,
		'expand_all' => false,
		'print'      => false
	);
	
	global $explore_styles, $explore_scripts;
	$_options = array_merge($explore_default_options, $_options);
	
	if (isset($_options['title'])) {
		$title = $_options['title'];
	}
	else {
		$var_name = print_var_name($var);
		if ($var_name)
			$title = "Exploring \"$var_name\"";
		else
			$title = "Exploring " . gettype($var);
	}

	$htmlOutput = <<<EOF
	$explore_styles
	<h2 class='explore_title'>$title</h2>
EOF;

	if ($_options['enable_js']) {
		$htmlOutput .= '<a class="all_links" href="javascript:explore_showAll();">Expand all details</a>
		&nbsp;
		<a class="all_links" href="javascript:explore_hideAll();">Collapse all</a>
		<br /><br />';
	}

	$htmlOutput .= explore_var2table($var, $_options);

	if ($_options['enable_js']) {
		$htmlOutput .= $explore_scripts;

		if ($_options['expand_all']) {
			$htmlOutput .= '<script type="text/javascript">explore_showAll();</script>';
		}
	}

	if ($_options['print']) {
		print $htmlOutput;
	}
	else {
		return $htmlOutput;
	}
}

function explore_json_url($json_url, $_options = array()){
	$feed = file($json_url);
	if (!isset($_options['title'])) $_options['title'] = 'Exploring JSON feed: ' . $json_url;
	return explore_json($feed[0], $_options);
}

function explore_json($json_string, $_options = array()){
	$json_data = json_decode($json_string);
	if (!isset($_options['title'])) $_options['title'] = 'Exploring JSON string';
	return explore($json_data, $_options);
}

function explore_var2table($mixed_var, $options = array()){
	if (empty($mixed_var)) return NULL;

	$output_id = md5(microtime());

	$output = NULL;

	$show_hide = ($options['enable_js']
		AND isset($options['deep']) AND $options['deep']);

	if ($show_hide) {
		$output .= '<a href="javascript:explore_showDetails(\'' . $output_id . '\');"
			class="explore_show_link" id="link_show_' . $output_id . '">
			+ Explore ' . gettype($mixed_var) . '</a>';
		$output .= '<a href="javascript:explore_hideDetails(\'' . $output_id . '\');"
			class="explore_hide_link" id="link_hide_' . $output_id . '"
			style="display: none;">- Hide Details</a>';
	}

	$output .= '<table id="' . $output_id . '" class="grid
		' . ($show_hide ? 'details_table':'') . '">';
	foreach($mixed_var as $key => $value) {
		if (is_array($value) || is_object($value))
			$inner = explore_var2table($value, array_merge($options, array('deep' => TRUE)));
		else
			$inner = $value;

		if ($inner === FALSE) $inner = 'FALSE';
		if ($inner === TRUE)  $inner = 'TRUE';
		if ($inner === NULL)  $inner = '<i>NULL</i>';

		if ($options['show_type']) {
			$type = strtolower(gettype($value));
			$typeHtml = "<td class='type' valign='top'>$type</td>";
		}
		else $typeHtml = NULL;

		$output .= "<tr>
			<td class='label' valign='top'>$key</td>
			$typeHtml
			<td>$inner</td>
		</tr>\n";
	}
	$output .= '</table>';

	return $output;
}

function print_var_name($var) {
	foreach($GLOBALS as $var_name => $value) {
		if ($value === $var) {
			return $var_name;
		}
	}

	return false;
}

class PC_explore {

	static function get_styles() {
		$explore_styles = <<<EOF
<style type="text/css">
.all_links, .explore_title, table.grid {
	font: 14px/18px 'Helvetica Neue', Helvetica, Arial, Sans-serif;
}

.explore_title {
	font-size: 24px;
	font-weight: bold;
}

table.grid {
	border-collapse: collapse;
	border-bottom: 1px solid #ddd;
}

table.grid td {
	padding: 3px 15px 3px 7px;
	text-shadow: #fff 0 1px 0;
	background: #feffff;
	background: -moz-linear-gradient(top, #feffff 0%, #f5f5f5 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #feffff), color-stop(100%, #f5f5f5));
	background: -webkit-linear-gradient(top, #feffff 0%, #f5f5f5 100%);
	background: linear-gradient(top, #feffff 0%, #f5f5f5 100%);
	border-top: 1px solid #ddd;
	border-left: 1px solid #eee;
}

table.grid td  > table.grid,
.explore_show_link,
.explore_hide_link {
	margin: -4px -15px -4px -8px;
}

table.grid tr.head td {
	color: #fff;
	text-shadow: #000000 0 -1px 0;
	background: #b5bdc8;
	background: -moz-linear-gradient(top, #b5bdc8 0%, #828c95 36%, #28343b 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #b5bdc8), color-stop(36%, #828c95), color-stop(100%, #28343b));
	background: -webkit-linear-gradient(top, #b5bdc8 0%, #828c95 36%, #28343b 100%);
	background: linear-gradient(top, #b5bdc8 0%, #828c95 36%, #28343b 100%);
	border: none;
	border-right: 1px solid rgba(255, 255, 255, 0.2);
	white-space: nowrap;
}

table.grid tr.head td:first-child {
	border-top-left-radius: 10px;
}

table.grid tr.head td:last-child {
	border-top-right-radius: 10px;
}

table.grid td.label,
table.grid td.type {
	background: #dddddd;
	background: -moz-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #dddddd), color-stop(100%, #afafaf));
	background: -webkit-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: -o-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: -ms-linear-gradient(top, #dddddd 0%, #afafaf 100%);
	background: linear-gradient(top, #dddddd 0%, #afafaf 100%);
}

.details_table {
	display: none;
}

table.grid td.label {
	font-weight: bold;
}

.explore_show_link,
.explore_hide_link {
	display: block;
	color: #333;
	padding: 3px 5px;
	text-decoration: none;
}

.explore_show_link {
	background: rgb(255,255,224); /* Old browsers */
	background: -moz-linear-gradient(top, rgba(255,255,224,1) 0%, rgba(255,255,136,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,224,1)), color-stop(100%,rgba(255,255,136,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* IE10+ */
	background: linear-gradient(top, rgba(255,255,224,1) 0%,rgba(255,255,136,1) 100%); /* W3C */
}

.explore_hide_link {
	background: rgb(222,239,255); /* Old browsers */
	background: -moz-linear-gradient(top, rgba(222,239,255,1) 0%, rgba(152,190,222,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(222,239,255,1)), color-stop(100%,rgba(152,190,222,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* IE10+ */
	background: linear-gradient(top, rgba(222,239,255,1) 0%,rgba(152,190,222,1) 100%); /* W3C */
	margin-bottom: 4px;
}

.all_links {
	color: #333;
}
</style>
EOF;
		
		return $explore_styles;
	}
	
	static function get_javascript() {
		$explore_scripts = <<<EOF
<script type="text/javascript">
function explore_showDetails(output_id){
	document.getElementById(output_id).style.display = 'block';
	document.getElementById('link_hide_' + output_id).style.display = 'block';
	document.getElementById('link_show_' + output_id).style.display = 'none';
}

function explore_hideDetails(output_id){
	document.getElementById(output_id).style.display = 'none';
	document.getElementById('link_hide_' + output_id).style.display = 'none';
	document.getElementById('link_show_' + output_id).style.display = 'block';
}

function explore_showAll(){
	show_links = document.getElementsByClassName('explore_show_link');
	hide_links = document.getElementsByClassName('explore_hide_link');
	tables = document.getElementsByClassName('details_table');

	for (var i = 0; i < tables.length; i++) tables[i].style.display = 'block';
	for (var i = 0; i < show_links.length; i++) show_links[i].style.display = 'none';
	for (var i = 0; i < hide_links.length; i++) hide_links[i].style.display = 'block';
}

function explore_hideAll(){
	show_links = document.getElementsByClassName('explore_show_link');
	hide_links = document.getElementsByClassName('explore_hide_link');
	tables = document.getElementsByClassName('details_table');

	for (var i = 0; i < tables.length; i++) tables[i].style.display = 'none';
	for (var i = 0; i < show_links.length; i++) show_links[i].style.display = 'block';
	for (var i = 0; i < hide_links.length; i++) hide_links[i].style.display = 'none';
}
</script>
EOF;
		return $explore_scripts;
	}

}
