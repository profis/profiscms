<?php
/** ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$cfg['core']['no_login_form'] = true;
require_once 'admin.php';
if (!$auth->Authorize('core', 'admin')) {
	die('No access');
}
$cfg['debug_mode'] = false;
header('Cache-Control: no-cache');
$site_id = $_POST['site'];
$r = $db->prepare("SELECT theme FROM {$cfg['db']['prefix']}sites s WHERE id=?");
$r->execute(array($site_id));
if (!$r) die('Database error');
if (!$r->rowCount()) die('Site was not found.');
$stylesheet = '../themes/'.$r->fetchColumn().'/custom.css';
function rgb2hex_callback($rgb) {
	$r = $rgb[1]; $g = $rgb[2]; $b = $rgb[3];
    return strtoupper("#".str_pad(dechex($r),2,"0",STR_PAD_LEFT).str_pad(dechex($g),2,"0",STR_PAD_LEFT).str_pad(dechex($b),2,"0",STR_PAD_LEFT));
}
//update styles
if (isset($_POST['styles'])) {
	$j = json_decode($_POST['styles'], true);
	$sheet = '';
	foreach ($j as $style) {
		//get style data
		$class = $style[0];
		$tag = $style[1];
		$styles = $style[2];
		$all = $style[3]; //style for all sites
		$locked = v($style[4]); //locked style (substyles like tr, td)
		//continue
		if (in_array($tag , array('tr','td')) and $locked) {
			$tag = 'table';
		}
		if (strpos($class, ' ' . $tag) !== false) {
			$tag = '';
		}
		$key = (!empty($tag)?$tag:'').".$class";
		$sheet .= $key.'{'.$styles.'}';
	}
	//replace color rgb format to hex
	$sheet = preg_replace_callback("/rgb\(([0-9]+),\s([0-9]+),\s([0-9]+)\)/i", 'rgb2hex_callback', $sheet);
	//save
	if (file_put_contents($stylesheet, $sheet) === false) {
		echo 'Error while saving';
	}
}
//return json stylesheet to cms
require(CORE_ROOT . "classes/CSSParser.php");
$out = array();
if (is_file($stylesheet)) {
	$sheet = new CSSParser(file_get_contents($stylesheet));
	$parsed = $sheet->parse();

	$sheet_selectors = $parsed->getAllSelectors();
	$sheet_rules = $parsed->getAllRuleSets();

	for ($a=0; isset($sheet_selectors[$a]); $a++) {
		$selector = $sheet_selectors[$a]->getSelector();
		$selector = explode('.', $selector[0]);
		$class = $selector[1];
		$class_parts = explode(' ', $class);
		$locked = false;
		if (in_array(v($class_parts[1]), array('tr', 'td'))) {
			$tag = $class_parts[1];
			$locked = true;
		}
		else $tag = $selector[0];
		
		$rules = $sheet_rules[$a]->getRules();
		$style = '';
		foreach ($rules as &$rule) {
			$style .= $rule->__toString();
		}
		$out[] = array($class, $tag, $style, $locked);
		//class, tag, style, all sites, locked from editing (tr, td)
	}
}
//print_pre($out); return;
echo preg_replace_callback("/rgb\(([0-9]+),\s([0-9]+),\s([0-9]+)\)/i", 'rgb2hex_callback', json_encode($out));