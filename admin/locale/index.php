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
//default language
header('Content-Type: text/javascript; charset=utf-8');
$default_ln = 'en';
//prefered language
$ln = rtrim($_SERVER['REDIRECT_QUERY_STRING'], '/');
if (!preg_match("#^[a-z]{2}$#", $ln) || $ln == $default_ln) {
	unset($ln);
}
//extjs
if (isset($ln)) {
	$f = '../ext/src/locale/ext-lang-'.$ln.'.js';
	if (is_file($f)) {
		echo file_get_contents($f),"\n";
	} else {
		$f = '../ext/src/locale/ext-lang-en.js';
		echo file_get_contents($f),"\n";
	}
} else {
	$f = '../ext/src/locale/ext-lang-en.js';
	echo file_get_contents($f),"\n";
}
//Profis CMS localization
$f = 'PC.en.js';
echo file_get_contents($f),"\n";
if (isset($ln)) {
	$f = 'PC.'.$ln.'.js';
	if (is_file($f)) {
		echo file_get_contents($f),"\n";
	}
}
//admin/tiny_mce/langs/*.js			admin/tiny_mce/langs/en.js			neverta gaist laiko ir prievartauti tinymce