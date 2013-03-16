<?php

global $cfg, $db, $memstore, $cache, $core, $auth, $plugins, $routes, $site, $page, $gallery;

$memstore = new PC_memstore; // used only to store values temporarily within process memory (previously was $cache = new PC_cache;)
$cache = isset($cfg["cache"]["class"]) ? new $cfg["cache"]["class"] : new PC_cache;

$core = new PC_core;

$auth = new PC_auth;

$plugins = new PC_plugins;

$plugins->debug = true;
$plugins->set_instant_debug_to_file($cfg['path']['logs'] . 'base/plugins.html');

$plugins->Scan();


$routes = new PC_routes;

$site = new PC_site;
$page = new PC_page;
$gallery = new PC_gallery;

?>