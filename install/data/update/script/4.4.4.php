<?php 
/**
 * Update to v4.4.4
 *
 * Adds configuration variables 'recaptcha_private_key' and 'recaptcha_public_key' to the database.
 *
 * @var array $cfg
 * @var PC_core $core
 * @var PC_site $site
 * @var PC_page $page
 * @var PC_gallery $gallery
 * @var PC_routes $routes
 * @var PC_auth $auth
 * @var PC_memstore $memstore
 * @var PC_cache $cache
 * @var PC_plugins $plugins
 * @var PC_database $db
 */
$core->Set_config_if('recaptcha_public_key', '', 'forms');
$core->Set_config_if('recaptcha_private_key', '', 'forms');

