<?php

if (!function_exists('v')) {
	function v(&$var, $default=null, $if_empty='') {
		if (isset($var)) {
			if ($if_empty === '') return $var;
			elseif (empty($var)) return $if_empty;
			else return $var;
		}
		else return $default;
	}
}

class PC_debug {
	public $debug = false;
	public $debug_forced = false;
	public $debug_level = 0;
	public $debug_groups = array();
	public $debug_state_stack = array();
	public $debug_string_stack = array();
	public $debug_string = '';
	public $debug_wrap_all_begin = '<div>';
	public $debug_wrap_all_end = '</div>';
	public $debug_entity_wrap_begin = '<div #indent_string#>';
	public $debug_entity_wrap_end = '</div>';
	
	public $exec_times = array();
	public $inner_exec_times_summaries = array();
	public $group_time_data = array();
	var $exec_times_wrap_all_begin = '<div>';
	var $exec_times_wrap_all_end = '</div>';
	var $exec_times_wrap_begin = '<div #indent_string#>';
	var $exec_times_wrap_end = '</div>';
	
	
	public $last_time = 0;

	public $instant_debug_to_file = false;
	public $file = '';
	
	public $debug_level_offset = 0;
	
	public static $explored = false;
	
//	function __construct($debug) {
//		$this->debug = $debug;
//	}

	
	public function set_console_debug() {
		$this->debug_wrap_all_begin = '';
		$this->debug_wrap_all_end = '';
		$this->debug_entity_wrap_begin = "\n";
		$this->debug_entity_wrap_end = '';
	}
	
	public function set_debug_offset($ofset = 0) {
		$this->debug_level_offset = $ofset;
	}
	
	public function increase_debug_offset($incr = 1) {
		$this->debug_level_offset += $incr;
	}
	
	public function absorb_debug_settings(PC_debug $logger, $debug_level_offset = 0) {
		$this->debug = $logger->debug;
		$this->debug_forced = $logger->debug_forced;
		$this->instant_debug_to_file = $logger->instant_debug_to_file;
		$this->file = $logger->file;
		$this->debug_level = $logger->debug_level;
		$this->debug_level_offset = $logger->debug_level_offset + $debug_level_offset;
	}
	
	/**
	 *
	 * @param string $string
	 * @param string $group_indent = ''
	 * @return <type>
	 */
	function debug($string, $group_indent = '') {
		global $cfg;
		if (!$this->debug_forced) {
			if (!$this->debug or !v($cfg['debug_output']) or isset($cfg['debug_ip']) and $_SERVER['REMOTE_ADDR'] != $cfg['debug_ip']) {
				return;
			}
		}
		
				
		$debug_group = $group_indent;
		$indent = 0;
		if (strpos($group_indent, '_') !== false) {
			list($debug_group, $indent) = explode('_', $group_indent);
		}
		
		if (empty($indent) and is_numeric($debug_group)) {
			$indent = $debug_group;
			$debug_group = '';
		}
		$indent = (int) $indent;
		
		$indent += $this->debug_level_offset;
		
		if ($this->debug_level > 0 and $this->debug_level < $indent) {
			return;
		}
		if ($debug_group != '' and is_array($this->debug_groups) and !in_array($debug_group, $this->debug_groups)) {
			return;
		}
		
		$wrap_begin = str_replace('#indent_string#', self::_get_indent_string($indent), $this->debug_entity_wrap_begin);
		if (is_array($string) or is_object($string)) {
			$exported_var = '';
			try {
				$exported_var = var_export($string, true);
			} catch (Exception $exc) {
				
			}
			$this->debug_string .= $wrap_begin . '<pre>' . $exported_var . '</pre>' . $this->debug_entity_wrap_end;
		} else {
			$this->debug_string .= $wrap_begin . $string . $this->debug_entity_wrap_end;
		}
		if ($this->instant_debug_to_file) {
			try {
				@file_put_contents($this->file, $this->debug_string, FILE_APPEND);
			}
			catch(Exception $e) {

			}
			$this->clear_debug_string();
		}
	}
	
	function get_debug_query_string($query, $params) {
		$keys = array();
		$values = array();
		
		$string_keys = array();
		$string_values = array();

		# build a regular expression for each parameter
		foreach ($params as $key => $value) {
			$value = addslashes($value);
			if (is_string($key)) {
				$string_keys[] = '/:'.$key.'/';
				if (!is_numeric($value)) {
					$value = "'$value'";
				}
				$string_values[$key] = $value;
			} else {
				$keys[] = '/[?]/';
				if (!is_numeric($value)) {
					$value = "'$value'";
				}
				$values[$key] = $value;
			}
		}

		$query = preg_replace($keys, $values, $query, 1, $count);
		$query = preg_replace($string_keys, $string_values, $query);
		return $query;
	}
	
	function debug_query($query, $params, $group_indent = '') {
		$query = $this->get_debug_query_string($query, $params);			
		$this->debug($query, $group_indent);
	}
	
	function debug_time($group_indent = '') {
		$this->debug('=='. date('Y-m-d H:i:s') .'=============================', $group_indent);
	}
	
	function debug_time_and_ip($group_indent = '') {
		$this->debug('=='. date('Y-m-d H:i:s') .'===  ' . pc_ip() . '   ===========', $group_indent);
	}
	
	protected function _get_indent_string($indent) {
		$px = 20 * ($indent);
		$s = 'style="margin-left:' . $px . 'px;"';
		return $s;
	}
	
	public function set_debug($debug) {
		$this->debug = $debug;
	}
	
	function get_debug_string($forced = false) {
		if ($forced or !empty($this->debug_string)) {
			$class = '';
			if (function_exists('get_called_class')) {
				$class = get_called_class();
			}
			return $this->debug_wrap_all_begin . '<strong>Class: ' . $class . '</strong>' .  $this->debug_string . $this->debug_wrap_all_end;
		}
	}
	
	function clear_debug_string() {
		$this->debug_string = '';
	}

	function set_new_debug($debug) {
		array_push($this->debug_state_stack, $this->debug);
		array_push($this->debug_string_stack, $this->debug_string);
		$this->clear_debug_string();
		$this->debug = $debug;
	}

	function restore_debug() {
		if (!empty($this->debug_state_stack)) {
			$this->debug = array_pop($this->debug_state_stack);
		}
		if (!empty($this->debug_string_stack)) {
			$this->debug_string = array_pop($this->debug_string_stack);
		}
	}
	
	function set_instant_debug_to_file($file_name, $append = null, $wait_seconds = 0) {
		$this->instant_debug_to_file = true;
		$this->file = $file_name;
		if (@file_exists($this->file)) {
			if ($append or time() - filemtime($this->file) <= $wait_seconds) {
				$append = true;
				$this->debug('=='. date('Y-m-d H:i:s') .'=============================');
				$this->increase_debug_offset(1);
			}
		}
		
		$this->file_put_debug($this->file, $append);
	}
	
	function file_put_debug($file_name = '', $append = null) {
		global $cfg;
		if (!$this->debug_forced) {
			if (!$this->debug or !v($cfg['debug_output']) or isset($cfg['debug_ip']) and $_SERVER['REMOTE_ADDR'] != $cfg['debug_ip']) {
				return;
			}
		}
		if (empty($file_name)) {
			$file_name = $this->file;
		}
		if ($append) {
			$append = FILE_APPEND;
		}
		$pre_s = '';
		$file_name_parts = explode('.', $file_name);
		if (!$append and $file_name_parts[count($file_name_parts) - 1] == 'html') {
			$pre_s .= '<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			======================'. date('Y-m-d H:i:s') .'=============================';
		}
		try {
			@file_put_contents($file_name, $pre_s . $this->get_debug_string(), $append);
		}
		catch(Exception $e) {
			
		}
		
	}
	
	function clear_time_data() {
		$this->exec_times = array();
		$this->group_time_data = array();
	}
	
	public function get_callstack($backtrace = null) {
		if (is_null($backtrace)) {
			$backtrace = debug_backtrace();
		}
		$s = '';
		foreach ($backtrace as $key => $value) {
			v($value['file']);
			v($value['line']);
			$s .= "<br /> {$value['file']}:{$value['line']}: {$value['function']}";
		}
		return $s;
	}
	
		/**
	 * Rememeber intermiate time
	 *
	 * @param string $title
	 * @return string
	 */
	function click($title='', $group = '', $inner_times_summary = '') {
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];

		if (!empty($group)) {
			if (!isset($this->group_time_data[$group])) {
				$this->group_time_data[$group] = array(
					'count' => 0,
					'total_time' => 0
				);
			}
			$this->group_time_data[$group]['count']++;
			$this->group_time_data[$group]['total_time'] += $mtime - $this->last_time;
			$this->last_time = $mtime;
			return;
		}
		
		if (trim($title) == '') {
			$this->exec_times[] = $mtime;
			$this->inner_exec_times_summaries[] = $inner_times_summary;
		} else {
			$this->exec_times[$title] = $mtime;
			$this->inner_exec_times_summaries[$title] = $inner_times_summary;
		}
			

		
		$this->last_time = $mtime;
		
		return $mtime;
	}

	/**
	 * Get all times summary
	 * @return string
	 */
	function get_exec_times_summary() {
		$string = '';
		if (is_array($this->exec_times)) {
			$first_key = '';
			$old_key = '';
			foreach ($this->exec_times as $key => $value) {
				if ($old_key == '') {
					$first_key = $key;
				}
				if ($old_key != '') {
					$diff = $this->exec_times[$key] - $this->exec_times[$old_key];
					$diff = self::round_time($diff);
					$string.= $this->exec_times_wrap_begin . " " . $diff . "  $key" . $this->exec_times_wrap_end;
					if (isset($this->inner_exec_times_summaries[$key]) and !empty($this->inner_exec_times_summaries[$key])) {
						$wrap_begin = $this->exec_times_wrap_begin;
						$wrap_begin = str_replace('#indent_string#', self::_get_indent_string(2), $wrap_begin);
						$string.= $wrap_begin . " " . $this->inner_exec_times_summaries[$key] . $this->exec_times_wrap_end;
					}
				}
				$old_key = $key;
			}
			if (v($this->exec_times[$old_key]) and v($this->exec_times[$first_key])) {
				$diff = $this->exec_times[$old_key] - $this->exec_times[$first_key];
				$diff = self::round_time($diff);
				$string = $this->exec_times_wrap_begin . " <strong>Time summary for $first_key</strong> " . $this->exec_times_wrap_end . $string;
				$string.= $this->exec_times_wrap_begin . " <strong>Total execution time</strong> " . $diff . $this->exec_times_wrap_end;
			}
		}
		if (!empty($this->group_time_data)) {
			$string.= $this->exec_times_wrap_begin . "<strong> Group total times: </strong>" . $this->exec_times_wrap_end;
			foreach ($this->group_time_data as $key => $value) {
				$string.= $this->exec_times_wrap_begin . " $key:  " . self::round_time($value['total_time']) . " (count: {$value['count']})" . $this->exec_times_wrap_end;
			}
		}
		
		return $this->exec_times_wrap_all_begin . $string . $this->exec_times_wrap_all_end;
	}
	
	public static function round_time($time) {
		$time = floatval($time);
		if ($time < 0.00001 or strpos($time, 'E') !== false) {
			$time = '0.00000...';
		}
		else {
			$time = round($time, 6);
		}
		return $time;
	}
	
	public static function array_to_string($my_array, $sep1=',', $sep2=':') {
		$my_string = '';

		$substring_array = array();

		if (!is_array($my_array)) {
			return $my_string;
		}

		foreach ($my_array as $key => $value) {
			$substring_array[] = $key . $sep2 . $value;
		}

		if (count($substring_array) > 0) {
			$my_string = implode($sep1, $substring_array);
		}
		return $my_string;
	}
	
	public function explore(&$var) {
		require_once CMS_ROOT . 'libs/explore/explore.php';
		if (!class_exists('PC_explore')) {
			return;
		}
		$s = '';
		if (!self::$explored) {
			$s .= PC_explore::get_styles();
			$s .= PC_explore::get_javascript();
		}
		$s .= explore($var);
		self::$explored = true;
		return $s;
	}
	
}

?>
