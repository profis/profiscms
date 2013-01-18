<?php

define('DS', DIRECTORY_SEPARATOR);
define('CORE_DIR', 'core');

define('CMS_ROOT', dirname(dirname(__FILE__)) . DS);
define('CORE_ROOT', CMS_ROOT . CORE_DIR . DS);

define('PLUGINS_ROOT', CMS_ROOT . 'plugins');
define('CORE_PLUGINS_ROOT', CORE_ROOT . 'plugins' . DS);

?>
