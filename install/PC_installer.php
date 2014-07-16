<?php


class PC_installer {
	
	public $min_php_version = '5.2';
	public $min_mysql_version = '5.1.x';
	public $min_gd_version = '2';
	
	public $requirements_passed;
	
	public function __construct() {
		$this->requirements_passed = true;
	}
	
	public function is_installed() {
		include PC_CONFIG_FILE;
		if (isset($cfg['db']) and isset($cfg['db']['name']) and !empty($cfg['db']['name']) and is_string($cfg['db']['name'])) {
			return true;
		}
		return false;
	}
	
	public function get_config() {
		include CORE_ROOT . 'config/system_config.php';
		include PC_CONFIG_FILE;
		include CORE_ROOT . 'config/system_config_2.php';
		return $cfg;
	}
	
	public function import_sql_file($file, $replacements = array()) {
		global $db;
		$sql = file_get_contents($file);
		$success = true;
		if ($sql) {
			if (!empty($replacements)) {
				$sql = str_replace(array_keys($replacements), array_values($replacements), $sql);
			}
			$queries = explode(';', $sql);
			foreach ($queries as $query) {
				if (!$success) {
					return $success;
				}
				if (!empty($query)) {
					$success = $db->query($query);
				}
			}
		}
		return $success;
	}
	
	public function validate_php_version(&$value) {
		$value = 'PHP ' . PHP_VERSION;
		return (PHP_VERSION >= $this->min_php_version);
	}
	
	public function validate_pdo() {
		$this->pdo_validated = $check = class_exists('PDO',false);
		return $check;
	}
	
	public function validate_pdo_mysql() {
		if ($this->pdo_validated) {
			$drivers = PDO::getAvailableDrivers();
			return $check = in_array('mysql', $drivers);
		}
		return false;
	}
	
	public function validate_mod_rewrite() {
		if (function_exists('apache_get_modules')) {
			return in_array('mod_rewrite', apache_get_modules());
		}
		else {
			ob_start();
			phpinfo(INFO_MODULES);
			$contents = ob_get_contents();
			ob_end_clean();
			return strpos($contents, 'mod_rewrite') !== false;
		}
		
	}
	
	public function validate_filter() {
		return function_exists('filter_var');
	}
	
	public function validate_mbstring() {
		return function_exists('mb_substr');
	}
	
	public function validate_mcrypt() {
		return function_exists('mcrypt_encrypt');
	}
	
	public function validate_gd(&$value) {
		if (!function_exists('gd_info')) {
			return false;
		}
		$gd_info = gd_info();
		if ($gd_info and isset($gd_info['GD Version'])) {
			$value = 'GD ' . $gd_info['GD Version'];
			preg_match_all('![\d\.]+!', $gd_info['GD Version'], $matches);
			if (isset($matches[0]) and isset($matches[0][0])) {
				$version = $matches[0][0];
				return ($version  >= $this->min_gd_version);
			}
			
		}
		return false;
	}
	
	public function validate_bcmath() {
		return function_exists('bcadd');
	}
	
	public function validate_iconv() {
		return function_exists('iconv');
	}
	
	public function validate_config_file_exists() {
		return file_exists(PC_CONFIG_FILE);
	}
	
	public function validate_config_file_writable() {
		return is_writable(PC_CONFIG_FILE);
	}
	
	public function get_validation_result($requirement) {
		global $t;
		$method = 'validate_' . $requirement;
		$value = '';
		$result =  $this->$method($value);
		if (!$result) {
			$this->requirements_passed = false;
		}
		if (empty($value)) {
			if ($result) {
				$value = $t['yes'];
			}
			else {
				$value =  $t['no'];
			}
		}
		return'<span class="'.(($result) ? 'text-success' : 'text-error') . '">'.$value.'</span>';

	}
	
}


?>