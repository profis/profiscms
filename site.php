<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http:#www.gnu.org/licenses/>.

$time = microtime(); $time = explode(" ", $time); $time = $time[1] + $time[0]; $start = $time;

require_once('base.php');

header("Content-Type: text/html; charset=utf-8");

//hooks
$core->Init_hooks('site_init');


if ($site->Identify(true) && $site->Render()) {
	$core->Init_hooks('site_render');
	require($site->data['tpl']);
}
if (v($cfg['debug_mode'])) {
	$time = microtime(); $time = explode(" ", $time); $time = $time[1] + $time[0]; $finish = $time; $totaltime = ($finish - $start);
	echo '<br /><span style="border: 1px dashed #e5e09b; background: #fffde0; color: #000000;">Page generated in <b>'.round($totaltime,3).'s</b></span><br /><br />';
	echo '<span style="border: 1px dashed #e5e09b; background: #fffde0; color: #000000;">Memory allocated for the script: <b>'.(memory_get_usage(true)/1024/1024).' MB</b> (peak: <b>'.(memory_get_peak_usage(true)/1024/1024).' MB</b>)</span>';
}