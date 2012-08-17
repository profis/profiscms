<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) if (!ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

// vvvvvvvvvv FILES LIST vvvvvvvvvv

$files = array();
$files[] = 'style.css';
$files[] = 'gallery.css';
$files[] = 'AwesomeUploader.css';
$files = array_merge($files, glob('Ext.ux.*.css'));

// ^^^^^^^^^^ FILES LIST ^^^^^^^^^^

$last_mod = 0;
foreach ($files as $f)
	$last_mod = max($last_mod, filemtime($f));

header('Content-Type: text/css');
header('Last-Modified: '.date('D, d M Y H:i:s O', $last_mod));
header('True-Last-Modified: '.date('D, d M Y H:i:s O', $last_mod));

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$cached_time = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	if ($last_mod == $cached_time) {
		header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified (pack styles)');
		exit;
	}
}

foreach ($files as $f) {
	echo "\n\n/***** $f *****/\n\n";
	readfile($f);
}
