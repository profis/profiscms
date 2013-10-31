<?php
/**
 * Utils
 * @version 2.2, 2011-09-12
 */

if (!function_exists('trace')) {
function trace($var, $return = false) {
	return PC_utils::trace($var, $return);
}
}

/**
 * Utility method class
 */
class PC_utils {
	
	static $monthNames = array('ru' => array("", "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль",
										"Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"),
								'en' => array("", "January", "February", "March", "April", "May", "June", "July",
										"August", "September", "October", "November", "December"));
	static $monthNames2 = array('ru' => array("", "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля",
										"Августа", "Сентября", "Октября", "Ноября", "Декабря"),
								'en' => array("", "January", "February", "March", "April", "May", "June", "July",
										"August", "September", "October", "November", "December"));
	static $daysNames = array('ru' => array("Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг",
										"Пятница", "Суббота", "Воскресенье"),
								'en' => array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday",
										"Friday", "Saturday", "Sunday"));
	static $yearChar = array( 'en' => 'm', 'ru' => 'г' );
	
	public static $last_send_email_error = '';
	
	/**
	 * Gets first photo from text.
	 * @param array|string content object as kay value pair array or text as string.
	 * @return string image url or null if no images found.
	 */
	static function getArticleImage($li, $size = null, $no_photo = null) {
		$text = is_array($li) ? (isset($li['text']) ? $li['text'] : '') : $li;
		if ($text && preg_match('#<img[^>]+src="([^"]+)"[^>]*>#i', $text, $m) && trim($m[1])) {
			$img = trim($m[1]);
		} else if ($no_photo) {
			$img = $no_photo;
		}
		if (isset($img) && $img) {
			if ($size)
				$img = preg_replace('#^(.*/)(?:thumb-[^/]*|large|small|thumbnail)(/[^/]+)$#i', '${1}thumb-'.preg_quote($size).'${2}', $img);
			
			return $img;
		}
		return null;
	}
	
	/**
	 * Gets list of images from text.
	 * 
	 * @param array|string $li content object as kay value pair array or text as string.
	 * @param string $field field name to get images from when $li is gived as array.
	 * @param array|string $size gallery thumbnail types to return.
	 * @param bool $recursive if true then will try to get images recoursively from parents if no images was found in current item.
	 * @return array image url list.
	 */
	static function getTextImages($li, $field = 'text', $size = null, $extended = false, $recursive = false) {
		global $page;
		$text = is_array($li) ? (isset($li[$field]) ? $li[$field] : '') : $li;
		$m = null;
		if ($text && preg_match_all('#<img[^>]+src="([^"]+)"[^>]*>#i', $text, $m)) {
			$list = $m[1];
			foreach ($list as $idx => $img) {
				$img_big = null;
				if (isset($size) && !empty($size)) {
					if (is_array($size)) {
						$_size = $size;
						$img = preg_replace('#^(.*/)(?:thumb-[^/]*|large|small|thumbnail)(/[^/]+)$#i', '${1}thumb-'.preg_quote(array_shift($_size)).'${2}', $img);
						if (!empty($_size))
							$img_big = preg_replace('#^(.*/)(?:thumb-[^/]*|large|small|thumbnail)(/[^/]+)$#i', '${1}thumb-'.preg_quote(array_shift($_size)).'${2}', $img);
					} else {
						$img = preg_replace('#^(.*/)(?:thumb-[^/]*|large|small|thumbnail)(/[^/]+)$#i', '${1}thumb-'.preg_quote($size).'${2}', $img);
					}
				}
				if ($extended) {
					$m1 = null;
					$title = preg_match('#[^>]+title="([^"]+)"#i', $m[0][$idx], $m1) ? $m1[1] : '';
					$alt = preg_match('#[^>]+alt="([^"]+)"#i', $m[0][$idx], $m1) ? $m1[1] : '';
					$list[$idx] = array('src' => $img, 'title' => $title, 'alt' => $alt);
					if (isset($img_big)) $list[$idx]['src_big'] = $img_big;
				} else {
					$list[$idx] = $img;
				}
			}
			return $list;
		} else if ($recursive && is_array($li) && isset($li['idp']) && $li['idp']) {
			
			if (($li_ = $page->Get_page($li['idp'])) || ($li_ = $page->Get_content($li['idp']))) {
				return self::getTextImages($li_, $field, $size, $extended, $recursive);
			}
			
		}
		return array();
	}
	
	static function getLastMonthDate($year, $month) {
		$time = mktime(0, 0, 0, intval($month) + 1, 0, intval($year));
		return date('Y-m-d', $time);
	}
	
	/**
	 * Get short text.
	 * 
	 * @param string $text text to be shortened.
	 * @param int $maxLen maximum short text length in characters.
	 * @param string $append string to append if text was shortened (default: "...").
	 * @return string short text.
	 */
	static function shortText($text, $maxLen = 100, $append = null) {
		if (is_null($append)) $append = '...';
		$text = strip_tags($text);
		$textLen = mb_strlen($text);
		if ($textLen > $maxLen) {
			$_text = mb_substr(strip_tags($text), 0, $maxLen);
			$text = preg_replace('#[\ \.,:;\)\-"\'>\?\!]+$#i', '', $_text);
			$text = trim($text, '.,:;- ').((!empty($text) && $textLen > $maxLen) ? $append : '');
		}
		
		return $text;
	}
	
	
	/**
	 * Utf-8 version of str_word_count().
	 * @param string $string
	 * @param integer $format Specify the return value of this function. The current supported values are:<br>
	 * 0 - returns the number of words found<br>
	 * 1 - returns an array containing all the words found inside the string<br>
	 * 2 - returns an associative array, where the key is the numeric position of the word inside the string and the value is the actual word itself.
	 * 
	 * @param boolean $include_symbols - 
	 * 	 if true, some specific symbols (Math, *, Number) can be allowed at the beginning and in the rest part of the words	 	 
	 */
	public static function str_word_count_utf8($string, $format = 0, $include_symbols = false) {
		$word_count_mask = "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u";

		if ($include_symbols) {
			$word_count_mask = "/[\p{L}\p{N}\*][\p{L}\p{Mn}\p{Pd}\p{N}\p{Sc}\p{So}\*'\x{2019}]*/u";
		}

		switch ($format) {
			case 1: {
				preg_match_all($word_count_mask, $string, $matches);
				return $matches[0];
			}
			case 2: {
				preg_match_all($word_count_mask, $string, $matches, PREG_OFFSET_CAPTURE);
				$result = array();
				foreach ($matches[0] as $match) {
					//correct offsets for multi-byte characters (`PREG_OFFSET_CAPTURE` returns *byte*-offset)
					//we fix this by recounting the text before the offset using multi-byte aware `strlen`
					$correct_offset = mb_strlen(substr($string, 0, $match[1]), 'utf-8');
					
					$result[$correct_offset] = $match[0];
				}
				return $result;
			}
		}
		return preg_match_all($word_count_mask, $string, $matches);
	}
	
	
	/**
	 * Extrack id's from array of key value pair array by key 'pid'.
	 * $param array $page array of key value pair arrays.
	 * @return array array of id's
	 */
	static function extractIds($pages) {
		$list = array();
		foreach ($pages as $f) {
			$list[] = $f['pid'];
		}
		return $list;
	}
	
	/**
	 * Hex encode string, useful for encoding email to protect from bots.
	 * 
	 * @param string $str string to be encoded.
	 * @param bool $ent if true string will be encoded as html entities, if false string will be encoded as url.
	 * @return string encoded string.
	 */
	static function hexEncode($str, $ent = false) {
		$encoded = '';
		$strlen =  function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
		$substr =  function_exists('mb_substr') ? 'mb_substr' : 'substr';
		$ch = $ent ? '&#x' : '%';
		$length = $strlen($str);
		for ($i = 0; $i < $length; $i++) {
			$encoded .= $ch.wordwrap(bin2hex($substr($str, $i, 1)), 2, $ch, true).($ent ? ';' : '');
		}
		return $encoded;
	}
	
	/**
	 * Constructs fuly qualified url
	 * 
	 * @param string $path request uri
	 * @param array|string $params query string as key value pairs array or as string
	 * @param string $lang language code
	 * @return string url
	 */
	static function getUrl($path = null, $params = null, $lang = null) {
		$slash = '/';
		if (substr($path, -1) == '/' or strpos($path, '?') !== false) {
			$slash = '';
		}
		$url =
			($path ? "$path" . $slash : '');
		if (!$params || !is_array($params) && $params != '_all') $params = array();
		if ($params) {
			if ($params == '_all') {
				$params = $_SERVER["QUERY_STRING"];
			}
			else {
				if (!is_array($params)) $params = self::urlParamsToArray($params);
				$params = empty($params) ? '' : self::urlParamsToString($params);
			}
			$question = '?';
			if (strpos($url, '?') !== false) {
				if (substr($url, -1) == '?') {
					$question = '';
				}
				else {
					$question = '&';
				}
				
			}
			$url .= $params ? ($question . $params) : '';
		}
		
		$url = $url;
		return $url;
	}
	
	/**
	 * Constructs current fully qualified url
	 * 
	 * @param array|string $params query string as key value pairs array or as string
	 * @param bool $stripGet if true current query string params will not be included
	 * @param string $path request uri to replace current
	 * @param string $lang language code to replace current
	 * @return string url
	 */
	static function getCurrUrl($params = null, $stripGet = false, $path = null, $lang = null) {	
		global $site;
		//$ru = ($site && $site->loaded_page) ? $site->loaded_page['route'] : '';
		$ru = rtrim($site->Get_current_link(), '/');
		$path = $path ? $path : $ru;
		if ($params) {
			if (isset($params['page']) and $params['page'] > 1) {
				$path .= '/page' . $params['page'];
				unset($params['page']);
			}
			if (!is_array($params)) $params = self::urlParamsToArray($params);
		} else {
			$params = array();
		}
		if (!$stripGet) {
			$params = array_merge($_GET, $params);
			unset($params['page']);
		}
		
		$url = self::getUrl($path, $params, $lang);
		
		return $url;
	}
	
	/**
	 * Strips domain and protocol from url.
	 * 
	 * @param string $url url to be striped.
	 * @param bool $stripFirstSlash if true result will not have begining slash.
	 * @return string resulting url
	 */
	static function getUrlRequestUri($url, $stripFirstSlash = false) {
		// strip protocol
		$url0 = preg_replace('#^(http|https)://#i', '', trim($url));
		// strip domain
		$url = trim(preg_replace('#^[^/]*/#i', '/', trim($url0)), '/');
		return (!$stripFirstSlash ? '/' : '').$url;
	}
	
	/**
	 * Converts key value pairs array to query string
	 * @param array $params key value pairs array
	 * @return string query string
	 */
	static function urlParamsToString($params) {
		if (!is_array($params)) return $params;
		if (empty($params)) {
			return '';
		}
		$prm = '';
		foreach ($params as $k => $v) {
			if (!$k || is_null($v) || $v === false) continue;
			$prm .= ($prm ? '&' : '').urlencode($k).(($uv = urlencode($v)) ? ('='.$uv) : '');
		}
		$prm = str_replace(array('%2F', '%2C', '%7B', '%24', '%7D'), array('/', ',', '{', '$', '}'), $prm);
		return $prm ? $prm : false;
	}
	
	/**
	 * Converts query string to key value pairs array
	 * @param string $params query string
	 * @return array key value pairs array
	 */
	static function urlParamsToArray($params) {
		if (is_array($params)) return $params;
		$params = explode('&', $params);
		$prm = Array();
		for ($i = 0, $count = count($params); $i < $count; $i++) {
			$p = explode('=', $params[$i], 2);
			$prm[trim($p[0])] = isset($p[1]) ? $p[1] : '';
		}
		return $prm ? $prm : false;
	}
	
	/**
	 * Paging helper. Call order: 1.
	 * Generate paging definition key value pair array. You should pass result of this method to list getter funtion that supports paging trouth $filter parameter as reference width 'paging' key.
	 * @param int $cpp item count per page.
	 * @param int $max_paging_items maximum pages to show in paging switcher. Usful when there is lots of pages and narrow space for paging switcher.
	 * @return array paging definition key value pair array.
	 */
	static function pagingInit($cpp = null, $max_paging_items = 5) {
		$paging = Array(
			'cpp'	=> isset($_GET['cpp']) ? intval($_GET['cpp']) : (isset($cpp) ? $cpp : 5),
			'pi'	=> (isset($_GET['page']) && intval($_GET['page'])) ? intval($_GET['page']) : 1,
			'pc'	=> $max_paging_items,
			'ps'	=> 0,
			'pe'	=> 0,
			'total'	=> 0,
			'pages'	=> 0
		);
		return $paging;
	}
	
	/**
	 * Paging helper. Call order: 2.
	 * Calculates and completes paging definition. If you pass $paging previously passed to list getter function $paging['total'] should be filled width number of items throuth all pages, so you donot need to pass $total_items.
	 * @param array $paging paging definition as key value pair array. If not specified it will be generated, but for that you have to pass $total_items.
	 * @param int $total_items total items throuth all pages. If not specified $paging['total'] will be used.
	 * @param int $max_paging_items maximum pages to show in paging switcher. Usful when there is lots of pages and narrow space for paging switcher. If not specified $paging['pc'] will be used.
	 * @return array paging definition key value pair array.
	 */
	static function pagingGet(&$paging, $total_items = null, $max_paging_items = null) {
		if (!$paging || !is_array($paging)) {
			if (isset($max_paging_items)) {
				$paging = self::pagingInit(null, $max_paging_items);
			} else {
				$paging = self::pagingInit(null);
			}
		}
		if (isset($max_paging_items))
			$paging['pc'] = $max_paging_items;
		if (isset($total_items))
			$paging['total'] = $total_items;
		$paging['pages'] = intval($paging['cpp'])
			? ceil(intval($paging['total']) / intval($paging['cpp'])) : 1;
		$paging['pi'] = min(intval($paging['pi']), $paging['pages']);
		if ($paging['pi'] <= 0) $paging['pi'] = 1;
		$ps = $paging['pi'] - floor($paging['pc'] / 2); if ($ps < 1) $ps = 1;
		$pe = $paging['pi'] + floor($paging['pc'] / 2);
		if ($pe > $paging['pages']) {
			$pe = $paging['pages'];
			if (($pe - $ps) < $paging['pc']) { $ps = $paging['pages'] - $paging['pc']; if ($ps < 1) $ps = 1; }
		} else {
			if (($pe - $ps) < $paging['pc']) { $pe = $ps + ($paging['pc'] - 1); if ($pe > $paging['pages']) $pe = $paging['pages']; }
		}
		
		$paging['ps'] = $ps;
		$paging['pe'] = $pe;
		
		return $paging;
	}
	
	/**
	 * Get variable value
	 */
	static function gvv($key, $arr = false, $def = '', $html_spec_chars = false, $not_empty = false) {
		$val = $def;
		if ($arr === false) {
			global $$key;
			$val = (isset($$key) && (!$not_empty || !empty($$key))) ? $$key : $def;
		} else if (is_array($arr)) {
			$val = (isset($arr[$key]) && (!$not_empty || !empty($arr[$key]))) ? $arr[$key] : $def;
		} else if (is_object($arr)) {
			$val = (isset($arr->$key) && (!$not_empty || !empty($arr->$key))) ? ($arr->$key) : $def;
		}
		return $html_spec_chars ? htmlspecialchars($val) : $val;
	}
	
	/**
	 * Outputs html css styled, code colored hideable box with passed $var value.
	 * @param mixed $var variable to output.
	 * @param bool $return if true result is returned instead of outputing.
	 * @return string if $return is true returns html of the result, else null;
	 */
	static function trace($var, $return = false) {
		$code = '';
		if (is_array($var) || is_object($var)) {
			$code .= '<pre style="margin: 0;">'.print_r($var, true).'</pre>';
		} else {
			$code .= $var;
		}
		if (is_bool($var)) $code = '<span style="color: #0000ff; font-weight: bold; font-style: normal; font-family: Courier New; font-size: 12px;">'.($var ? 'TRUE' : 'FALSE').'</span>';
		if (is_null($var)) $code = '<span style="color: #0000ff; font-weight: bold; font-style: normal; font-family: Courier New; font-size: 12px;">NULL</span>';
		
		$code = '<div style="padding: 0px; margin: 4px 0; position: relative; float: none; clear: both; border: 1px dashed #e5e09b;">'.
				'<a style="display: block; position: absolute; right: 3px; top: 2px; font-weight: bold; text-decoration: none; line-height: 14px; color: #676767; font-family: arial,sans-serif; font-size: 19px;" href="#" onclick="this.parentNode.style.display = \'none\'; return false;" title="Hide">'.
					'&times;'.
				'</a>'.
				'<div id="FormMessages_message" style="padding: 17px 20px; margin: 0; float: none; background: #fffde0; color: #000000; font-family: Arial; font-size: 13px; font-style: italic;">'.$code.'</div>'.
			'</div>';
		
		if ($return) {
			return $code;
		} else {
			echo $code;
			return null;
		}
	}
	
	/**
	 * Write log to file.
	 * 
	 * Every new call appends message to the end of the file.
	 * Every message is writen to new line.
	 * Every message is prepended width current date and time.
	 * 
	 * @param string $msg message to log.
	 * @param string $file path to file to write log to. If omited 'debug.log' is used.
	 */
	static function debugLog($msg, $file = null) {
		if (!$file) {
			$file = 'debug.log';
		}
		if (is_array($msg) || is_object($msg)) {
			$msg = print_r($msg, true);
		}
		if (($fl = @fopen($file, 'at+'))) {
			fputs($fl, "[".date('Y-m-d H:i:s')."] $msg\n");
			fclose($fl);
		}
	}
	
	/**
	 * Convert key value pair array to object.
	 * 
	 * @param array $arr key value pair array to convert.
	 * @return object
	 */
	static function arrayToObject($arr) {
		$obj = new stdClass();
		foreach ($arr as $k => $v) {
			if (!$k || is_numeric($k)) continue;
			$obj->$k = $v;
		}
		return $obj;
	}
	
	/**
	 * Get file in a plugin path.
	 * 
	 * @param string plugin file (ex.: [plugin_folder]/[file_path_relative_to_plugin_folder]).
	 * @return string path to plugin file.
	 */
	static function modPath($path) {
		global $core;
		$m = null;
		$res = preg_match('#^([^/]*)/(.*)$#i', $path, $m) ? $m : null;
		if (!$res) return null;
		return $core->Get_path('plugins', $res[2], $res[1]);
	}
	
	/**
	 * Last error info from executed DB helper functions
	 * 
	 * @var array
	 * @link http://php.net/manual/en/pdo.errorinfo.php php.net manual PDO::errorInfo
	 */
	private static $dbLastErrorInfo_ = null;
	
	/**
	 * Get raw last database error
	 * 
	 * @return array
	 * @link http://php.net/manual/en/pdo.errorinfo.php php.net manual PDO::errorInfo
	 */
	static function dbLastErrorInfoRaw() {
		return self::$dbLastErrorInfo_;
	}
	
	/**
	 * Get parsed last database error
	 * 
	 * @return string
	 */
	static function dbLastErrorInfo() {
		if (self::$dbLastErrorInfo_) {
			if (isset(self::$dbLastErrorInfo_[2]) && self::$dbLastErrorInfo_[2]) {
				$err = self::$dbLastErrorInfo_[2];
			} else if (isset(self::$dbLastErrorInfo_[1]) && self::$dbLastErrorInfo_[1]) {
				$err = self::$dbLastErrorInfo_[1];
			} else if (isset(self::$dbLastErrorInfo_[0]) && intval(self::$dbLastErrorInfo_[0])) {
				$err = self::$dbLastErrorInfo_[0];
			} else {
				$err = null;
			}
			return $err;
		}
		return null;
	}
	
	/**
	 * DB helper function
	 */
	static function dbInsert($data, $table, $_db = null) {
		global $db;
		if (!$_db || !is_object($_db)) $_db = $db;
		$quot = ($_db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') ? '"' : '`';
		$columns = '';
		$values = '';
		$values_v = array();
		foreach ($data as $key => $value) {
			if (is_array($value) || is_object($value)) $value = serialize($value);
			$values .= ($values ? ", " : "").":$key";
			$values_v[":$key"] = $value;
			$columns .= ($columns ? ", " : "")."{$quot}$key{$quot}";
		}
		$r = $_db->prepare($q="INSERT INTO {$quot}$table{$quot} ( $columns ) VALUES ( $values )");
		$r->execute($values_v);
		self::$dbLastErrorInfo_ = $r->errorInfo();
		return $_db->lastInsertId();
	}
	
	/**
	 * DB helper function
	 */
	static function dbUpdate($data, $where, $table, $_db = null) {
		global $db;
		if (!$_db || !is_object($_db)) $_db = $db;
		$quot = ($_db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') ? '"' : '`';
		$values = '';
		$values_v = array();
		foreach ($data as $key => $value) {
			if (is_array($value) || is_object($value)) $value = serialize($value);
			$values_v[":$key"] = $value;
			$values .= ($values ? ", " : "")."{$quot}$key{$quot}=:$key";
		}
		$r = $_db->prepare("UPDATE {$quot}$table{$quot} SET $values".($where ? " WHERE $where" : ""));
		$r->execute($values_v);
		self::$dbLastErrorInfo_ = $r->errorInfo();
	}
	
	/**
	 * Redirect to url. Actions:
	 * <code>
	 * 		- closes session;
	 * 		- writes header location header;
	 * 		- terminates script execution width exit().
	 * </code>
	 * 
	 * @param string $url url to redirect to.
	 */
	static function redirect($url) {
		if (session_id()) session_write_close();
		header('Location: '.$url);
		exit();
	}
	
	static $sesUik = null;
	
	static function getSessionKey() {
		if (!self::$sesUik) {
			self::$sesUik = md5(__FILE__).'_pc_utils';
		}
		return self::$sesUik;
	}
	
	static function &setData($key, $value) {
		$sesUik = self::getSessionKey();
		if (!isset($_SESSION[$sesUik]) || !is_array($_SESSION[$sesUik])) {
			$null = null;
			$_SESSION[$sesUik] = &$null;
			$_SESSION[$sesUik] = Array();
		}
		$_SESSION[$sesUik][$key] = $value;
		return $_SESSION[$sesUik][$key];
	}
	
	static function &getData($key) {
		$sesUik = self::getSessionKey();
		$null = null;
		if (isset($_SESSION[$sesUik][$key]))
			return $_SESSION[$sesUik][$key];
		else
			return $null;
	}
	
	static function putMessage($key, $message, $type = '') {
		$sesUik = self::getSessionKey();
		if (!isset($_SESSION[$sesUik]) || !is_array($_SESSION[$sesUik])) {
			$null = null;
			$_SESSION[$sesUik] = &$null;
			$_SESSION[$sesUik] = Array();
		}
		if (!isset($_SESSION[$sesUik]['messages']))
			$_SESSION[$sesUik]['messages'] = Array();
		$_SESSION[$sesUik]['messages'][$key] = Array('msg' => $message, 'type' => $type);
	}
	
	static function getMessage($key) {
		$sesUik = self::getSessionKey();
		return isset($_SESSION[$sesUik]['messages'][$key])
			? $_SESSION[$sesUik]['messages'][$key]['msg'] : false;
	}
	
	static function getMessageType($key) {
		$sesUik = self::getSessionKey();
		return isset($_SESSION[$sesUik]['messages'][$key])
			? $_SESSION[$sesUik]['messages'][$key]['type'] : false;
	}
	
	static function removeMessage($key) {
		$sesUik = self::getSessionKey();
		unset($_SESSION[$sesUik]['messages'][$key]);
	}
	
	static function popMessage($key) {
		$msg = self::getMessage($key);
		self::removeMessage($key);
		return $msg;
	}
	
	static function hasMessage($key) {
		$sesUik = self::getSessionKey();
		return isset($_SESSION[$sesUik]['messages'][$key]);
	}
	
	
	/**
	 * Output language menu.
	 *
	 * The method is backwards compatible with older version: fourth parameter
	 * ($displayType) accepts text as well as boolean value. The possible values
	 * for that parameter are:
	 * <code>
	 * "name" or true - displays language names
	 * "code" or false - displays ISO 639-1 language codes
	 * "icon" - displays country associated icons
	 * "icon+name" - displays country associated icons followed by language names
	 * "icon+code" - displays country associated icons followed by ISO 639-1 language codes
	 * </code>
	 *
	 * Icon matrix settings is an associative array, containing keys:
	 * <code>
	 * image - URI or flags' matrix image, relative to root (default="admin/images/flags_matrix.png")
	 * width - width of each flag icon in the matrix (default=16)
	 * height - height of each flag icon in the matrix (default=11)
	 * xOffset - horizontal offset in pixels of top left corner, where flag icons start (default=0)
	 * yOffset - vertical offset in pixels of top left corner, where flag icons start (default=0)
	 * enCountryCode - country code for english language flag (default=gb)
	 * </code>
	 * All entries in matrix settings array are optional.
	 *
	 * @param array $menu menu item array to output.
	 * @param PC_site &$site PC_site instance.
	 * @param bool $hideCurr if true current language will be hidden from menu.
	 * @param mixed $displayType determines the final contents of language links. Can have these values: "name" / true, "code" / false, "icon", "icon+name", "icon+code"
	 * @param bool $onlyLinks if true then do not use UL LI structure.
	 * @param array|null $iconSettings used to change icon matrix settings. if null then default settings will be used
	 */
	static function htmlLangMenu($menu, &$site, $hideCurr = false, $displayType = "code", $onlyLinks = false, $iconSettings = null ) {
		$cc = 0;
		$html = '';
		if( $displayType === true ) $displayType = "name";
		else if( $displayType === false ) $displayType = "code";
		$useIcons = ($displayType == "icon" || $displayType == "icon+name" || $displayType == "icon+code");
		$useNames = ($displayType == "name" || $displayType == "icon+name");
		$useCode = ($displayType == "code" || $displayType == "icon+code");
		if( !$useIcons && !$useNames && !$useCode )
			$useCode = true;
		if( $useIcons ) {
			if( !is_array($iconSettings) )
				$iconSettings = Array();
			if( !isset($iconSettings["image"]) ) {
				$iconSettings["image"] = "admin/images/flags_matrix.png";
				$iconSettings["width"] = 16;
				$iconSettings["height"] = 11;
				$iconSettings["xOffset"] = 16;
				$iconSettings["yOffset"] = 11;
			}
			if( !isset($iconSettings["width"]) ) $iconSettings["width"] = 16;
			if( !isset($iconSettings["height"]) ) $iconSettings["height"] = 11;
			if( !isset($iconSettings["xOffset"]) ) $iconSettings["xOffset"] = 0;
			if( !isset($iconSettings["yOffset"]) ) $iconSettings["yOffset"] = 0;
			if( !isset($iconSettings["enCountryCode"]) ) $iconSettings["enCountryCode"] = "gb";
			
			$iconUrl = $site->cfg["url"]["base"] . $iconSettings["image"];
			$iconBaseStyles = "display:inline-block;vertical-align:middle;width:$iconSettings[width]px;height:$iconSettings[height]px;background:url($iconUrl) no-repeat ";
		}
		
		foreach ($menu as $k => $v) {
			$cls = array();
			if ($cc == 0) $cls[] = 'f';
			if ($k == $site->ln) $cls[] = 's';
			$i = ($useIcons && $k == "en") ? $iconSettings["enCountryCode"] : $k;
			if ($hideCurr && $k == $site->ln) continue;
			if (!$onlyLinks) {
				$html .= '<li'.(empty($cls) ? '' : (' class="'.implode(' ', $cls).'"')).'>';
			}
			$html .= '<a'.(empty($cls) ? '' : (' class="'.implode(' ', $cls).'"')).
						' href="'.htmlspecialchars($site->Get_link(null, $k)).
						'" title="'.htmlspecialchars($v).'">'.
						($onlyLinks ? '' : '<span>').
							($useIcons ? ('<i style="' . $iconBaseStyles . (-$iconSettings["xOffset"] - $iconSettings["width"] * (ord($i[0]) - 0x61)) . 'px ' . (-$iconSettings["yOffset"] - $iconSettings["height"] * (ord($i[1]) - 0x61)) . 'px;"></i>') : '').
							($useNames ? $v : ($useCode ? $k : "")).
						($onlyLinks ? '' : '</span>').
					'</a>';
			if (!$onlyLinks) {
				$html .= '</li>';
			}
			$cc++;
		}
		echo ($onlyLinks ? $html : '<ul>'.$html.'</ul>');
	}
	
	/**
	 * Output menu.
	 * 
	 * Menu item structure:
	 * <code>
	 * Array(
	 * 		['name'] => ...,		// Type: string. Required. Name of menu item.
	 * 		['pid'] => ...,			// Type: int. Required. ID of menu item.
	 * 		['route'] => ...,		// Type: string. Required (if url is set optional). Route of menu item.
	 * 		['url'] => ...,			// Type: string. Optional. Url of menu item (if not set route will be used to generate it).
	 * 		['hot'] => ...,			// Type: bool. Optional. Adds style class hot.
	 * 		['controller'] => ...,	// Type: string. Optional. Adds controller as style class.
	 * 		['sub'] => ...,			// Type: array. Optional. Submenu items (if not set will be retreved automaticly using $page->Get_submenu(pid)).
	 * 		['sel'] => ...,			// Type: bool. Optional. Adds style class s (if not set will be retreved automaticly using $site->Is_opened(pid)).
	 * 		['curr'] => ...			// Type: bool. Optional. Adds style class c (if not set will be retreved automaticly using ($site->loaded_page['pid'] == pid)).
	 * )
	 * </code>
	 * 
	 * @param array $menu menu item array to output.
	 * @param PC_site &$site PC_site instance.
	 * @param int $maxLvl maximum level to output menu items (levels counted from 1).
	 * @param bool $popup if true then all submenus will be added event from unselected menu items, to use css/js popup menu.
	 * @param int $lvl internal level counter. (DO NOT SET THIS PARAMETER).
	 */
	static function htmlMenu($menu, &$site, $maxLvl = -1, $popup = false, $lvl = 0) {
		global $page;
		$cc = 0;
		$html = '';
		
		foreach ($menu as $li) {
			if (empty($li['name'])) continue;
			$isOpen = isset($li['sel'])
				? ($li['sel'] ? true : false)
				: $site->Is_opened($li['pid']);
			$isCurr = isset($li['curr'])
				? ($li['curr'] ? true : false)
				: (isset($li['pid']) && ($site->loaded_page['pid'] == $li['pid']));
			if (isset($li['url']) && $li['url']) {
				$url = $li['url'];
			} else {
				$url = (isset($li['route']) && $li['route'])
					? $site->Get_link($li['route']) : $site->Get_link_prefix();
			}
			
			//papildytas
			$target = '';
			if (isset($li['redirect']) && $li['redirect'] != '') {
				if (strpos($li['redirect'], 'http') !== false){
					$target = 'target="_blank"';
				}
			}

			$cls = array();
			if (isset($li['controller']) && !empty($li['controller'])) { $cls[] = $li['controller']; }
			if ($cc == 0) { $cls[] = 'f'; }
			if ($isOpen) { $cls[] = 's'; }
			if ($isCurr) { $cls[] = 'c'; }
			if (isset($li['hot']) && $li['hot']) { $cls[] = 'hot'; }
			if ((!isset($li['sub']) || !is_array($li['sub'])) && ($isOpen || $popup)
				&& isset($page) && is_object($page) && ($maxLvl < 0 || $maxLvl > ($lvl + 1))) {
				$li['sub'] = $page->Get_submenu($li['pid']);
			}
			$subHtml = (($isOpen || $popup) && isset($li['sub']))
				? self::htmlMenu($li['sub'], $site, $maxLvl, $popup, $lvl + 1) : '';
			if (!isset($li['controller']) || $li['controller'] != 'top_menu_bottom') { // greitas paprastas ifas tam kad nerodyti virsuje vieno mygtuko o apacioje jis butu matomas (koks tolkas is meniu dublikavimo.....?)
				$html .= '<li'.(empty($cls) ? '' : (' class="'.implode(' ', $cls).'"')).'>'.
						'<a'.(empty($cls) ? '' : (' class="'.implode(' ', $cls).'"')).
							($isCurr ? '' : (' href="'.htmlspecialchars($url).'"')).
							' title="'.htmlspecialchars($li['name']).'" '.$target.'><span>'.
							$li['name'].'</span></a>'.
							$subHtml.
					'</li>';
				$cc++;
			}
		}
		$html_ = $html ? ('<ul>'.$html.'</ul>') : '';
		if ($lvl > 0) {
			return $html_;
		}
		echo $html_;
	}
	
	static function getRequestData($fields, $request = 'post') {
		$fields = array_flip($fields);
		if ($request == 'post') {
			return array_intersect_key($_POST, $fields);
		}
		else {
			return array_intersect_key($_GET, $fields);
		}
		
	}
	
	static function filterArray($fields, $data) {
		$fields = array_flip($fields);
		return array_intersect_key($data, $fields);
	}
	
	/**
	 * Method modifies email recipients and message for debugging.
	 * $cfg['debug_email'] array is taken into account. Its elements:
	 * <ul>
	 *	<li>enable: must be true for debugging</li>
	 *	<li>email: must be set for debugging</li>
	 *	<li>ip_md5: (optional) md5 of visitor ip</li>
	 *	<li>ip_pattern: (optional) preg_match pattern of visitor ip</li>
	 * </ul>
	 * @global type $cfg
	 * @param array $recipients
	 * @param string $message
	 * @param string $alt_message
	 */
	static function debugEmail(&$recipients, &$message, &$alt_message = '') {
		global $cfg;
	
		if (isset($cfg['debug_email']) and is_array($cfg['debug_email']) and v($cfg['debug_email']['enable']) and v($cfg['debug_email']['email'])) {
			$forms_debug_email = $cfg['debug_email']['email'];
			$debug = false;
			$checked = false;
			if (isset($cfg['debug_email']['ip_md5'])) {
				$checked = true;
				if (md5($_SERVER["REMOTE_ADDR"]) == $cfg['debug_email']['ip_md5']) {
					$debug = true;
				}
			}
				
			if (isset($cfg['debug_email']['ip_pattern'])) {
				$checked = true;
				if (preg_match($cfg['debug_email']['ip_pattern'], $_SERVER["REMOTE_ADDR"])) {
					$debug = true;
				}
			}
			
			if (!$checked) {
				$debug = true;
			}
			
			if($debug) {
				$message .= "<p style='color:red;'>Message is sent to $forms_debug_email for debugging.<br>It would be sent to: ";
				if (is_array($recipients)) {
					foreach ($recipients as $key=>$mailer){
						$recipients[$key] = $forms_debug_email;
						$message .= $mailer." ";
					}
				}
				else {
					$real_recipient = $recipients;
					$recipients = $forms_debug_email;
					$message .= $real_recipient." ";
				}
				
				$message .= "</p>";
			}  
		}
	}
	
	/**
	 * Method for sending email. 
	 * @global type $cfg
	 * @param string $recipient single or multiple emails separated by semicolon (';')
	 * Can be also an array of recipient emails.
	 * @param string $message
	 * @param array $params required keys: 'from_email', 'from_name', 'subject'. 
	 * Optional keys: 'charset' 
	 * @param array $tags Template replacements for the message.
	 * For example if $tags = array('foo' => 'bar'),
	 * then '{foo}' occurrences will be replaced to 'bar' in the message
	 */
	static function sendEmail($recipient, $message, $params = array(), $tags = array()) {
		global $cfg;
		$logger = new PC_debug;
		$logger->debug = true;
		$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'send_mail.html');
		//$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'send_mail.html', true, 10);
		$logger->debug('sendEmail');
		
		if (is_array($recipient)) {
			$emails = $recipient;
		}
		else {
			$recipient = str_replace(",", ";", $recipient);
			$emails = explode(';', $recipient);
		}
		
		self::debugEmail($emails, $message);
		
		$markers = array();
		foreach ($tags as $key => $tag) {
			if (is_array($tag)) {
				unset($tags['key']);
				continue;
			}
			$markers['{'.$key.'}'] = $tag;
		}
		$message = str_replace(array_keys($markers), array_values($markers), $message);
		
		require_once $cfg['path']['classes'] . 'class.phpmailer.php';
		
		$mail = new PHPMailer(); 
		
		if (isset($cfg['from_smtp']) and !empty($cfg['from_smtp'])) {
			require_once $cfg['path']['classes'] . 'class.smtp.php';
			$logger->debug("setting IsSMTP() and host", 1);
			$mail->IsSMTP();
			$mail->Host = $cfg['from_smtp'];
		}
		//$mail->SMTPDebug  = 1;
		$mail->From		= v($params['from_email'], v($cfg['from_email']));
		$mail->FromName	= v($params['from_name'], v($cfg['from_name']));
		$mail->Subject	= v($params['subject'], '');
		
		$logger->debug("mail->From: " . $mail->From, 1);
		$logger->debug("mail->FromName: " . $mail->FromName, 1);
		$logger->debug("mail->Subject: " . $mail->Subject, 1);
		
		if (isset($cfg['mailer_params']) and is_array($cfg['mailer_params'])) {
			foreach ($cfg['mailer_params'] as $key => $value) {
				$logger->debug("setting $key", 1);
				$mail->$key = $value;
			}
		}	
		
		$mail->AltBody = strip_tags($message);
		$mail->CharSet = v($params['charset'], 'utf-8');
		$mail->MsgHTML($message);
		
		$logger->debug($emails, 1);
		
		foreach ($emails as $key => $email) {
			$email = trim($email);
			if (!empty($email)) {
				$mail->AddAddress($email);
			}
		}
		$result = $mail->Send();
		if (!$result) {
			$logger->debug(':(', 2);
			$logger->debug("error: " . $mail->ErrorInfo, 3);
			$logger->debug(print_r(error_get_last(), true), 3);
			self::$last_send_email_error = $mail->ErrorInfo;
		}
		return $result;
	}
	
}

?>