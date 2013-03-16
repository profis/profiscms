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
	 * @param array|string content object as kay value pair array or text as string.
	 * @return array image url list.
	 */
	static function getTextImages($li, $size = null, $extended = false) {
		$text = is_array($li) ? (isset($li['text']) ? $li['text'] : '') : $li;
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
					$title = preg_match('#[^>]+title="([^"]+)"#i', $m[0][$idx], $m1) ? $m1[1] : '';
					$alt = preg_match('#[^>]+alt="([^"]+)"#i', $m[0][$idx], $m1) ? $m1[1] : '';
					$list[$idx] = array('src' => $img, 'title' => $title, 'alt' => $alt);
					if (isset($img_big)) $list[$idx]['src_big'] = $img_big;
				} else {
					$list[$idx] = $img;
				}
			}
			return $list;
		}
		return array();
	}
	
	static function getLastMonthDate($year, $month) {
		$time = mktime(0, 0, 0, intval($month) + 1, 0, intval($year));
		return date('Y-m-d', $time);
	}
	
	/**
	 * Get short text.
	 * @param string $text text to be shortened.
	 * @param int $maxLen maximum short text length in characters.
	 * @return string short text.
	 */
	static function shortText($text, $maxLen = 100) {
		$text = strip_tags($text);
		$textLen = mb_strlen($text);
		if ($textLen > $maxLen) {
			$text = mb_substr(strip_tags($text), 0, $maxLen);
			$text = preg_replace('#[\ \.,:;\)\-"\'>\?\!][^\ \.,:;\)\-"\'>\?\!]*$#i', '', $text);
			$text = trim($text, '.,:;- ').((!empty($text) && $textLen > $maxLen) ? '...' : '');
		}
		
		return $text;
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
	 * Constructs fuly qualified url
	 * 
	 * @param string $path request uri
	 * @param array|string $params query string as key value pairs array or as string
	 * @param string $lang language code
	 * @return string url
	 */
	static function getUrl($path = null, $params = null, $lang = null) {
		$slash = '/';
		if (substr($path, -1) == '/') {
			$slash = '';
		}
		$url =
			($path ? "$path" . $slash : '');
		if (!$params || !is_array($params)) $params = array();
		if ($params) {
			if (!is_array($params)) $params = self::urlParamsToArray($params);
			$params = empty($params) ? '' : self::urlParamsToString($params);
			$url .= $params ? ('?'.$params) : '';
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
	 * @param string $url url to be striped.
	 * @param bool $stripFirstSlash if true result will not have begining slash.
	 * @return string resulting url
	 */
	static function getUrlRequestUri($url, $stripFirstSlash = false) {
		// strip protocol
		$url = preg_replace('#^(http|https)://#i', '', trim($url));
		// strip domain
		$url = preg_replace('#^[^/]*/#i', '/', trim($url));
		$url = trim($url, '/');
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
	 * Get file in a plugin path.
	 * @param string plugin file (ex.: [plugin_folder]/[file_path_relative_to_plugin_folder]).
	 * @return string path to plugin file.
	 */
	static function modPath($path) {
		global $core;
		$res = preg_match('#^([^/]*)/(.*)$#i', $path, $m) ? $m : null;
		if (!$res) return null;
		$res = $core->Get_path('plugins', $res[2], $res[1]);
		return $res;
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
	static function debugEmail(array &$recipients, &$message, &$alt_message = '') {
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
				foreach ($recipients as $key=>$mailer){
					$recipients[$key] = $forms_debug_email;
					$message .= $mailer." ";
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
	static function sendEmail($recipient, $message, $params, $tags = array()) {
		global $cfg;
		
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
		$mail->From		= $params['from_email'];
		$mail->FromName	= $params['from_name'];
		$mail->Subject	= $params['subject'];
		
			
		$mail->AltBody = strip_tags($message);
		$mail->CharSet = v($params['charset'], 'utf-8');
		$mail->MsgHTML($message);
		
		foreach ($emails as $key => $email) {
			$email = trim($email);
			if (!empty($email)) {
				$mail->AddAddress($email);
			}
		}
		$mail->Send(); 
	}
	
}

?>