<?php
/**
 * @var PC_core $core
 * @var PC_plugins $plugins
 * @var array $cfg
 * @var string $plugin_name
 */
$metaFields = isset($cfg['site_users']['admin_editable_meta']) ? (is_array($cfg['site_users']['admin_editable_meta']) ? $cfg['site_users']['admin_editable_meta'] : array_map('trim', explode(',', $cfg['site_users']['admin_editable_meta']))) : array();
?>
var PC_plugin_site_users_meta_fields = <?php echo json_encode($metaFields); ?>;
