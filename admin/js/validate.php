<?php
//this script searches for the unneccessary , in the objects and arrays
$js = glob('*.js');
$js += glob('../../plugins/*/*.php');
$errors = 0;
foreach ($js as $file) {
	$source = file_get_contents($file);
	if (preg_match_all("#,\s*(}|])#mui", $source, $matches, PREG_OFFSET_CAPTURE)) {
		$errors++;
		echo '<h2>'.$file.'</h2>';
		print_r($matches);
		echo '<hr />';
	}
}
if ($errors < 1) {
	echo '<h1>No errors found</h1>';
}