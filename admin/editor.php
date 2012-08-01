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
require_once 'admin.php';
header('Content-Type: text/css');
header('Cache-Control: no-cache');
$r = $db->prepare("SELECT editor_width,editor_background FROM {$cfg['db']['prefix']}sites WHERE id=?");
$success = $r->execute(array($_GET['id']));
if ($success) {
	if ($r->rowCount() == 1) {
		$data = $r->fetch();
		echo 'body{padding:5px;background:'.$data['editor_background'].';width:'.$data['editor_width'].'px;}';
	}
}