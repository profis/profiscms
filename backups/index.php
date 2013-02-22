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
error_reporting(0); //ensure PHP won't output any error data and won't destroy structure
//no gzip & don't output login form if there's no active session
$cfg['core']['no_gzip'] = $cfg['core']['no_login_form'] = true;
require_once('../admin/admin.php'); //ensure the user is authorized, otherwise stop executing this script
//parse
$backup = $_SERVER['REDIRECT_QUERY_STRING'];
if (empty($backup)) return;
download_file(getcwd().'/'.$backup);