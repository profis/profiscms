<?php 


require dirname(__FILE__) . '/../../core/path_constants.php';
require CORE_ROOT . 'base.php';

$core->Set_config_if('recaptcha_public_key', '', 'forms');
$core->Set_config_if('recaptcha_private_key', '', 'forms');

