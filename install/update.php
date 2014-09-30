<?php
/**
 * Script detects current CMS database version and updates it to the version suited for current CMS version.
 * This script may get executed either by direct browser request or by include statement inside install.php.
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

if( isset($_REQUEST['cfg']) ) // check just in case
	die();

if( !defined('CORE_ROOT') )
	require dirname(__FILE__) . '/../core/path_constants.php';

if( !class_exists('PC_core') )
	require CORE_ROOT . 'base.php';

if( !isset($cfg['db']['name']) || empty($cfg['db']['name']) )
	echo 'Please run install script before trying to update.';


class PC_updater {
	protected $plugin;
	protected $path;

	public function PC_updater($updatesPath, $pluginName = '') {
		global $core;
		$this->plugin = $pluginName;
		$this->path = $updatesPath;
	}

	public function filterVersionNumber($filePath) {
		return preg_replace('#^.*/(.*)\\.[^\\.]*$#s', '$1', $filePath);
	}

	public function getAvailableUpdates() {
		$versions = array();
		if( is_dir($this->path) ) {
			foreach( glob($this->path . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir )
				$versions = array_merge($versions, array_map(array($this, 'filterVersionNumber'), glob($dir . '/*', GLOB_NOSORT)));
			$versions = array_unique($versions);
			usort($versions, 'version_compare');
		}
		return $versions;
	}

	public function detectCurrentSchemaVersion() {
		global $cfg, $db, $core;
		if( $db->getTableInfo('db_version') ) {
			$s = $db->prepare($q = "SELECT `version` FROM `{$core->db_prefix}db_version` WHERE `plugin` = :plugin");
			if( !$s->execute($p = array('plugin' => $this->plugin)) )
				throw new DbException($s->errorInfo(), $q, $p);
			if( $f = $s->fetch() )
				return $f['version'];
			else if( $this->plugin === '' )
				throw new Exception('Although `db_version` table exists it does not contain a record with current framework database version.');
		}

		if( is_file($f = $this->path . '/detect.php') ) {
			$version = include $f;
			return $version;
		}

		return null;
	}

	public function update() {
		global $cfg, $db, $core;

		$log = array();

		$versions = $this->getAvailableUpdates();
		if( empty($versions) ) {
			$log[] = "There are no updates or no setup folder in '{$this->plugin}' plugin directory.";
			return $log;
		}

		$dbVersion = $this->detectCurrentSchemaVersion();
		if( $dbVersion === null ) {
			$log[] = "Plugin is not installed (not activated at least one time)";
			return $log;
		}

		if( $this->plugin !== '' || version_compare($dbVersion, '4.5.0') >= 0 ) {
			// For the sake of sanity we insert the record with detected version
			$s = $db->prepare($q = "INSERT IGNORE INTO `{$core->db_prefix}db_version` (`plugin`, `version`) VALUES (:plugin, :version)");
			if( !$s->execute($p = array('plugin' => $this->plugin, 'version' => $dbVersion)) )
				throw new DbException($s->errorInfo(), $q, $p);
		}

		$s = $db->prepare($q = "UPDATE `{$core->db_prefix}db_version` SET `version` = :version WHERE `plugin` = :plugin");
		$dbType = v($cfg['db']['type'], 'mysql');
		// $log[] = "Database type: {$dbType}";
		$log[] = "Current schema version: {$dbVersion}";

		foreach( $versions as $version ) {
			if( version_compare($version, $dbVersion) <= 0 ) {
				$log[] = "Skipping update to {$version}";
				continue;
			}
			if( $this->plugin === '' && version_compare($version, PC_VERSION) > 0 ) {
				$log[] = "Stopping because next update version ({$version}) is greater than current CMS version (" . PC_VERSION . "). Please update CMS files and try updating once more.";
				break;
			}
			$log[] = "Updating to {$version}";

			if( is_file($f = $this->path . '/' . $dbType . '/' . $version . '.sql') ) {
				$log[] = "Importing SQL file {$f}";
				db_file_import(array($dbType => $f));
			}
			if( is_file($f = $this->path . '/script/' . $version . '.php') ) {
				$log[] = "Executing PHP script {$f}";
				include $f;
			}

			if( $this->plugin !== '' || version_compare($dbVersion, '4.5.0') >= 0 ) {
				if( !$s->execute($p = array('plugin' => $this->plugin, 'version' => $version)) )
					throw new DbException($s->errorInfo(), $q, $p);
			}
		}

		return $log;
	}
}

echo '<pre>';

echo "== Updating framework schema ==\n";
$updater = new PC_updater($core->Get_path('root', '/install/data/update'), '');
echo implode("\n", $updater->update()) . "\n\n";

foreach( glob(CORE_PLUGINS_ROOT . '*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir ) {
	$pluginName = basename($dir);
	if( is_dir($dir . '/setup/update') ) {
		echo "== Updating core plugin '{$pluginName}' schema ==\n";
		$updater = new PC_updater($dir . '/setup/update', $pluginName);
		echo implode("\n", $updater->update()) . "\n\n";
	}
}

foreach( glob(PLUGINS_ROOT . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir ) {
	$pluginName = basename($dir);
	if( is_dir($dir . '/setup/update') ) {
		echo "== Updating  plugin '{$pluginName}' schema ==\n";
		$updater = new PC_updater($dir . '/setup/update', $pluginName);
		echo implode("\n", $updater->update()) . "\n\n";
	}
}

// $core->Get_path('plugins', '/setup/update', $pluginName)

echo '</pre>';
