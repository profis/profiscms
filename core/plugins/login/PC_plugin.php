<?php

$thisPath =  dirname(__FILE__) . '/';
$clsPath = dirname(__FILE__).'/classes/';

Register_class_autoloader('PC_plugin_login_widget', $thisPath . 'widgets/PC_plugin_login_widget.php');
Register_class_autoloader('PC_plugin_login_form_widget', $thisPath . 'widgets/PC_plugin_login_form_widget.php');

