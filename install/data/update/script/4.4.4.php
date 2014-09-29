<?php 
/**
 * Update to v4.4.4
 *
 * Adds configuration variables 'recaptcha_private_key' and 'recaptcha_public_key' to the database.
 *
 * @var array $cfg
 * @var PC_core $core
 * @var PC_database $db
 * @var string[] $log
 */
$core->Set_config_if('recaptcha_public_key', '', 'forms');
$core->Set_config_if('recaptcha_private_key', '', 'forms');

