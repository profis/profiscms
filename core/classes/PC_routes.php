<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http:#www.gnu.org/licenses/>.

/**
* Class represents website routes maps, URIs, etc.
*/
final class PC_routes extends PC_debug{
	/**
	* Instance variable used to store in-memory routes information.
	*/
	public $list = array();
	/**
	* Instance variable used to store in-memory shifted routes information.
	*/
	public $shifted = array();
	/**
	* Instance variable used to store in-memory query string.
	*/
	public $get_request = '';
	/**
	* Instance variable used to remember a shift number in the current instance.
	*/
	private $shift = 0;
	/**
	* Instance variable used store a request (URN).
	*/
	private $request = '';
	/**
	* Class contstructor. Given only request. Inside constructor is called PC_routes::Parse_request()
	* @param mixed $request given to construct class based on that request.
	* @see PC_routes::Parse_request()
	*/
	public function __construct($request=null) {
		global $cfg;
		$this->debug = true;
		$this->set_instant_debug_to_file($cfg['path']['logs'] . 'router/routes.html', false, 5);
		$this->debug("__construct($request)");
		$this->Parse_request($request);
		
	}
	/**
	* Method used to set this instance variables $request and $get_request; if given parameter $request to the method - only instance 
	* variable $request.
	* @param mixed $request given request to set to instancce.
	* @return mixed TRUE if param $request was given and nothing otherwise.
	*/
	public function Set_request($request=null) {
		global $cfg, $core;
		if (is_null($request)) {
			$request_uri = explode('?', $_SERVER['REQUEST_URI']);
			$this->request = urldecode(substr($request_uri[0], strlen($cfg['base_path'])));
			$this->get_request = v($request_uri[1]);
		}
		else $this->request = $request;
		
		//echo $this->request . '<hr />';
				
		$get_vars = array(
			'ppage_get_var' => 'ppage',
			'page_get_var' => 'page'
		);
		
		foreach ($get_vars as $pattern => $get_var) {
			if (!isset($cfg['patterns'][$pattern])) {
				continue;
			}
			$page_match = preg_match('/'.$cfg['patterns'][$pattern].'/ui', $this->request, $matches);
		
			if ($page_match and $matches[1] and $matches[2]) {
				//print_r($matches);
				if (v($cfg['router']['no_trailing_slash']) and !empty($this->request) and substr($this->request, -1) == '/') {
					$url = rtrim($this->request, '/');
					$core->Redirect_local($url, 301);
				}
				elseif (!v($cfg['router']['no_trailing_slash']) and !empty($this->request) and substr($this->request, -1) != '/') {
					$url = $this->request;
					$url = pc_add_trailing_slash($url);
					$core->Redirect_local($url, 301);
				}
				$this->request = $matches[1];
				if (v($cfg['router']['no_trailing_slash'])) {
					pc_remove_trailing_slash($this->request);
				}
				$_GET[$get_var] = $matches[2];
			}
		}
		
		return true;
	}
	/**
	* Method used to combine given request to the routes map and to check every route for its' validity. Inside this method is 
	* called PC_routes::Set_request().
	* @param mixed $request given request to work with.
	* @see PC_routes::Set_request()
	*/
	public function Parse_request($request=null) {
		global $core, $cfg;
		$this->debug("Parse_request($request)");
		$this->Set_request($request);
		$this->debug("So, request is: " . $this->request);
		if (is_string($this->request)) {
			$this->list = explode('/', '/'.$this->request);
		}
		$this->debug($this->list, 1);
		if (!is_array($this->list)) {
			$this->list = array();
		}
		else {
			$redirect = false;
			for ($a=0; isset($this->list[$a]); $a++) {
				//allowed formats: any number or route i.e. 29, about-us, about_us, 871, hi, 4 etc
				if (!preg_match("#^(\pL+|\pN+|[\pL\pN][\pL\pN-_]{0,253}[\pL\pN])$#u", $this->list[$a])) {
					$this->debug("Unsetting $a: {$this->list[$a]} because allowed format was not satisfied", 2);
					if (defined('CMF_FRONTEND') and !empty($this->list[$a])) {
						$core->Redirect_local('', 301);
					}
					unset($this->list[$a]);
										
				};
			}
			array_unshift($this->list, null);
			if ($redirect) {
				$new_request = implode('/', $this->list);
				if (!empty($new_request)) {
					$new_request .= $cfg['trailing_slash'];
				}
				$this->debug("New request: " . $new_request, 2);
				if ($new_request != $this->request) {
					$this->debug("Redirecting to new request: " . $new_request, 3);
					$core->Redirect_local($new_request, 301);
				}
			}
			$route_count = count($this->list);
			if ($route_count > 0) {
				
			}
		}
		$this->debug("So, route list is:", 1);
		$this->debug($this->list, 2);
	}
	/**
	* Method used to get variable value $request of the instance.
	* @return string request of the instance.
	*/
	public function Get_request() {
		return $this->request;
	}
	/**
	* Method used to get route from the map by given route map level.
	* @param int $n given route map level to look for.
	* @return string route name.
	*/
	public function Get($n=null) {
		if (is_null($n)) {
			return $this->list;
		}
		if (isset($this->list[$n])) {
			return $this->list[$n];
		}
		return false;
	}
	/**
	* Method used to check if route map level exists in the route map by given route map level.
	* @param int $n given route map level to check for.
	* @return bool TRUE if exists, FALSE otherwise.
	*/
	public function Exists($n) {
		if (isset($this->list[$n])) return true;
		return false;
	}
	/**
	* Method used to store shifted routes map. This method shifts instance variable $routes twice and unshifts once.
	* Instance variable $shifted appended with shifted route. Instance shift counter $shift is also increased by one.
	* @return int $shift.
	*/
	public function Shift($times=1) {
		$times = (int)$times;
		if ($times > 0) for ($a=1; $a <= $times; $a++) {
			array_shift($this->list);
			array_push($this->shifted, array_shift($this->list));
			array_unshift($this->list, null);
			$this->shift++;
		}
		return $this->shift;
	}
	/**
	* Method which virtually is opposite to PC_routes::Shift() in terms of instance variables stages.
	* This method shifts instance variable $routes once and unshifts twice. 
	* Also, from instance variable $shifted is poped out once.
	* Instance shift counter $shift is also decreased by one.
	* @return int $shift.
	*/
	public function Unshift() {
		array_shift($this->list);
		array_unshift($this->list, array_pop($this->shifted));
		array_unshift($this->list, null);
		$this->shift--;
		return $this->shift;
	}
	/**
	* Method used to get current shift counter of the instance.
	* @return int @shift number of current shift.
	*/
	public function Get_shift() {
		return $this->shift;
	}
	/**
	* Method used to set current shift counter of the instance.
	* @param int @count.
	*
	* @toto implement this method.
	*/
	public function Set_shift($count) {
		//for cycle
	}
	/**
	* Method used to get reference to the route from the map by given route map level.
	* @param int $n given route map level to look for.
	* @return mixed reference to the route.
	*/
	public function &Get_reference($n) {
		if (is_null($n)) {
			return $this->list;
		}
		if (isset($this->list[$n])) {
			return $this->list[$n];
		}
	}
	/**
	* Method used to get routes map by given range; if range not given - all route map returned.
	* @param int $from given route map level to start.
	* @param int $to given route map level to end.
	* @param bool $array given indication how results will be returned; if TRUE given - returned array, string otherwise.
	* @return mixed route map.
	*/
	public function Get_range($from=null, $to=null, $array=false) {
		//total route parts
		$total = count($this->list)-1;
		//show from this part..
		if (is_null($from)) $from = 1;
		else $from = (int)$from;
		//..to this part
		if (is_null($to)) $to = $total;
		else $to = (int)$to;
		//format routes string or array
		if ($array) $out = array();
		else $out = '';
		for ($a=$from; $a<=$to; $a++) if (isset($this->list[$a])) {
			if ($array) $out[] = $this->list[$a];
			else $out .= $this->list[$a].'/';
		}
		return $out;
	}
	/**
	* Method used to get routes map levels count.
	* @return int count of the route map levels.
	*/
	public function Get_count() {
		return count($this->list)-1;
	}
	/**
	* Method used to get routes map by PC_base::site::route_shift.
	* @param bool $array given indication how results will be returned; if TRUE given - returned array, string otherwise.
	* @return mixed route map.
	*/
	public function Get_subroutes($array=false) {
		return $this->Get_range($this->site->route_shift+1, null, $array);
	}
	public function Get_last() {
		return $this->list[$this->Get_count()];
	}
	/**
	* Method used to check if routes map levels count conforms given number. Method PC_routes::Get_count() used in this method.
	* @param int $count given number to check for. 
	* @param bool $halt given indication if runtime should throw exception, if route map levels does not conform given number.
	* @return bool TRUE if routes map level count conforms given number, FALSE otherwise.
	* @see PC_routes::Get_count()
	*/
	public function Force_count($count, $halt=true) {
		if ($this->Get_count() != $count+1) {
			if ($halt) throw new PC_controller_exception('required_routes');
			$this->core->Show_error(404);
			return false;
		}
		else return true;
	}
	//routes count manager
	/**
	* Method used to check if routes map levels count equals to the given number.
	* @param int $count given number to check for.
	* @param bool $stop given indication if runtime should stop executing if route map levels does not equal to tge given number.
	* @return bool TRUE if routes map level count equal to the given number, FALSE otherwise.
	*/
	public function Check_count($count, $stop=true) {
		if (count($this->list) != (int)$count) {
			$this->core->Show_error('404');
			return false;
		}
		else return true;
	}
	//Get_range,
}