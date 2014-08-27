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

 define('CMF_FRONTEND', true);

require_once(CORE_ROOT . 'base.php');

$site->Add_header("Content-Type", "text/html; charset=utf-8");
ob_start();

$core->Init_hooks('site_init');
if ($site->Identify(true) && $site->Render()) {
	$core->Init_hooks('site_render');
	require($site->data['tpl']);
	$core->Init_hooks('site_after_render');
}

$html = ob_get_clean();

$core->Init_hooks('site_preprocess_html', array(
	'html' => &$html,
));

$html = $site->Process_site_html($html);

$core->Init_hooks('site_postprocess_html', array(
	'html' => &$html,
));

echo $html;

$time = microtime(); $time = explode(" ", $time); $time = $time[1] + $time[0]; $finish = $time; $totaltime = ($finish - $start);
$total_time = round($totaltime,3);



if (v($cfg['debug_time_to_file'])) {
	$width = round(100 * $total_time);
	$time_log_file = $cfg['path']['logs'] . '_time.html';
	$s = '';
	if (!file_exists($time_log_file)) {
		$s = '<head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /></head>';
	}
	
	$s .= '​======   ' . date('Y-m-d H:i:s') . '    ========<br />';
	$s .=	'<div style = "margin-left: 20px;">' . $total_time . ' - ' . $routes->Get_request().'</div>';
	$s .= '<div style = "margin-left: 20px; width: ' . $width . 'px; background-color: orange; height: 5px; line-height: 5px;">&nbsp;</div>​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​';
	@file_put_contents($time_log_file, $s, FILE_APPEND);
}

if (v($cfg['debug_mode'])) {
	echo '<br /><span style="border: 1px dashed #e5e09b; background: #fffde0; color: #000000;">Page generated in <b>'.$total_time.'s</b></span><br /><br />';
	echo '<span style="border: 1px dashed #e5e09b; background: #fffde0; color: #000000;">Memory allocated for the script: <b>'.(memory_get_usage(true)/1024/1024).' MB</b> (peak: <b>'.(memory_get_peak_usage(true)/1024/1024).' MB</b>)</span>';
}
//print_pre($_GET);
//print_pre($_SERVER);

