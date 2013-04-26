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
* Class skeleton for throwing more specific exceptions.
*/
class PC_controller_exception extends Exception {}
/**
* Class represents a site and friends. Pages, styles, routes, scripts, etc. are touched here.
*/
final class PC_site extends PC_base {
	/**
	* Field used for storing site data.
	*/
	public	$data = array();
	/**
	* Field used for storing loaded page route.
	*/
	public	$loaded_page = null;
	/**
	* Field used to store state if site should be rendered.
	*/
	public	$render = true;
	/**
	* Field for storing loaded page text.
	*/
	public	$text = '';
	/**
	* Field for storing page link prefix.
	*/
	public	$link_prefix = '';
	/**
	* Field for storing loaded page controller.
	*/
	public	$controller = null;
	/**
	* Field for storing route map level.
	*/
	public	$route_shift = 1;
	/**
	* Field for storing array of stylesheets.
	*/
	private $_stylesheets = array();
	/**
	* Field for storing array of scripts.
	*/
	private $_scripts = array();
	/**
	* Field which indicates if site page is loaded.
	*/
	private $_page_is_loaded = false;
	public $ln;
	
	
	/**
	 *
	 * @var array
	 */
	protected $_headers = array();
	
	
	/**
	* Method used to initialize a page. Inside this method is called PC_site::Load().
	* @see PC_site::Load()
	*/
	public function Init($id=null) {
		if (!is_null($id)) {
			$this->Load($id);
		}
		//$this->debug = true;
		//$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'site/site.html', false, 5);
	}
	
	/**
	 * Method for getting site id
	 * @return int
	 */
	public function get_id() {
		if (isset($this->data['id'])) {
			return $this->data['id'];
		}
		return false;
	}
	
	/**
	* Method used to load current page by given $route object. Method inside calls PC_site::Get_page_path() is called.
	* @param mixed $route given to set required variables and perform required tasks on page loading.
	* @return mixed return given $route with slightly modified $route['text'] value.
	* @see PC_site::Get_page_path()
	*/
	public function Load_page($route) {
		$this->debug("Load_page(", 2);
		$this->debug($route, 2);
		$this->core->Init_hooks('before_load_page', array(
			'page'=> &$route
		));
		$this->_page_is_loaded = true;
		//$route['text'] = preg_replace("/href=\"".$this->default_ln."\//", "href=\"",  $route['text']);
		$this->loaded_page = &$route;
		$nearest_published = false;
		if (isset($route['path'])) {
			$path_part_1 = $this->Get_page_path($route['path'][0]['idp'], false);
			foreach ($path_part_1 as $key => $part) {
				if ($part['published']) {
					$nearest_published = $part;
				}
				else {
					if ($nearest_published) {
						$this->debug('Redirecting to nearest published', 4);
						$this->core->Redirect_local($this->Get_link($nearest_published['route']));
					}
					else {
						$this->debug('Redirecting to home', 4);
						$this->core->Redirect_local($this->Get_home_link());
					}
				}
			}
			$path_part_2 = $route['path'];
			$this->loaded_page['route_path'] = array_merge($path_part_1, $path_part_2);
		}
		$this->plugin =& $this->loaded_page['controller'];
		$this->text =& $this->loaded_page['text'];
		$this->loaded_page['original_title'] = $this->loaded_page['title'];
		//freshly load page path, so we could know which menu items are opened
		//print_pre($this->loaded_page['route_path'][0]); die;
		//print_pre($path_part_1);
		//print_pre($path_part_2);
		//$this->loaded_page['path'] = array_merge($path_part_1, (count($this->loaded_page['path'])>1?array_slice($path_part_2, 1,
		//count($path_part_2)-1):$path_part_2));
		if (isset($route['path'])) $this->loaded_page['path'] = $this->loaded_page['route_path'];
		$this->debug('Init hook (after_load_page)', 2);
		$this->core->Init_hooks('after_load_page', array(
			'page'=> &$route
		));
		return $route;
	}
	/**
	* Method used to unload current page and set appropriate instance variables.
	* @return bool TRUE.
	*/
	public function Unload_page() {
		$this->loaded_page = null;
		$this->_page_is_loaded = false;
		return true;
	}
	/**
	* Method used to load a page. Inside this method is called PC_routes::Exists(), PC_routes::Get_range() and PC_site::Load_page_by_route().
	* @return mixed $route.
	* @see PC_routes::Exists()
	* @see PC_routes::Get_range()
	* @see PC_site::Load_page_by_route()
	*/
	public function Load_page_by_path() {
		$this->debug('Load_page_by_path(' . $this->routes->Get_request() . ')');
		$permalink_request = false;
		$hook_object = false;
		
		$this->page->absorb_debug_settings($this, 5);
		
		$request_trimmed = trim($this->routes->Get_request(), '/');
		
		if (!empty($request_trimmed)) {
			$ln = null;
			$permalink_page_id = $this->page->Get_id_by_content('permalink', $request_trimmed, $ln);
			if ($permalink_page_id) {
				$this->debug('Permalink page was found: ' . $permalink_page_id, 2);
				if (!is_null($ln)) {
					$this->ln = $ln;
				}
				$permalink_request = $this->page->Get_content_by_id($permalink_page_id, 'route', $this->ln);
				if ($permalink_request) {
					$this->debug('Page route was found: ' . $permalink_request, 3);
				}
			}
		}
		
		$ln = null;
		if (!$permalink_request) {
			
			$this->core->Init_hooks('core/site/request-from-permalink', array(
				'request'=> $this->routes->Get_request(),
				'permalink_request'=> &$permalink_request,
				'logger' => &$this,
				'ln' => &$ln
			));
			if ($hook_object) {
				$this->debug('Debug from hook [core/route/load-page-by-path] object');
				$this->debug($hook_object->get_debug_string(), 1);
			}
		}
		

		//return;
		if ($permalink_request) {
			if (!is_null($ln)) {
				$this->ln = $ln;
			}
			$this->debug("Parsing new request (ln is $ln): " . $permalink_request, 1);
			$this->routes->Parse_request($permalink_request);
		}
		
		$this->debug('Debug from page:', 6);
		$this->debug($this->page->get_debug_string(), 7);
		
		$this->debug('$this->routes->list:', 6);
		$this->debug($this->routes->list, 7);
		
		if ($this->routes->Exists(1)) {
			if ($this->routes->Exists(2)) {
				$path = $this->routes->Get_range(1);
				//try to select by this path
				/*$r = $this->prepare("SELECT pid FROM {$this->db_prefix}path_index WHERE path=? and ln=? LIMIT 1");
				$indexSupport = $r->execute(array($path, $this->ln));
				$this->indexSupport = $indexSupport;
				if ($indexSupport) {
					//this cms version supports indexed paths :)
					if ($r->rowCount()) {
						$pid = $r->fetchColumn();
						$route = $this->page->Get_page($pid);
					}
					else {
						//get page by what??
						//create index
						print_pre($this->route); die;
					}
				}
				else */$route = $this->Load_page_by_route($this->route[1], false, array(), false);
			}
			else $route = $this->Load_page_by_route($this->route[1], false, array(), false);
		}
		else $route = $this->Load_page_by_route(null, false, array(), false);
		return $route;
	}
	/**
	* Method used to load a page. Inside this method is called PC_routes::Do_action(), PC_page::Get_route_data().
	* @see PC_routes::Do_action()
	* @see PC_routes::Get_route_data()
	*/
	public function Load_page_by_route() {
		//get route data
		$args = func_get_args();
		$this->debug('Load_page_by_route(', 1);
		$this->debug($this->get_callstack(debug_backtrace()));
		$this->debug($args, 1);
		$route = call_user_func_array(array($this->page, 'Get_route_data'), $args);
		//analyze route data
		//core controller shouldnt be loaded
		if ($route['controller'] == 'core') {
			$this->debug($route, 2);
			$this->debug('Will do action', 2);
			$this->core->Do_action(v($route['action']), v($route['data']));
			
		}
		//dont open page if page language != current language
		/*elseif ($route['ln'] != $this->ln) {
			$this->core->Do_action('show_error', 404);
			return false;
		}*/
		else return $this->Load_page($route); //ar neuztenka return $route?
	}
	/**
	* Method used to check if site is loaded. This is done just by checking instance variable $data.
	* @return mixed TRUE if site is loaded, nothing otherwise.
	*/
	public function Is_loaded() {
		if (is_array($this->data) && count($this->data)) return true;
	}
	/**
	* Method used to check if page is loaded. This is done just by checking instance variable $loaded_page.
	* @return bool TRUE if page is loaded, FALSE otherwise.
	*/
	public function Page_is_loaded() {
		//this method not only check _page_is_loaded var, but also structure of page data
		if (is_array($this->loaded_page) && count($this->loaded_page)) {
			return $this->_page_is_loaded;
		}
		else return false;
	}
	/**
	* Method used identify current site. Inside this method is called PC_site::Get_by_domain() method. If site considered identified, 
	* method PC_site::Load_site_data() called, method PC_core::Show_error() otherwise.
	* @see PC_site::Get_by_domain()
	* @see PC_site::Load_site_data()
	* @see PC_core::Show_error()
	*/
	public function Identify($forceActive=false) {
		$site = $this->Get_by_domain();
		if (!$site || !$site['id']) {
			$this->core->Show_error('site_not_found');
			return false;
		}
		else return $this->Load_site_data($site, $forceActive);
	}
	/**
	* Method used to load site data by given site. In this method called PC_core::Get_theme_path() method.
	* @param mixed $site given to load.
	* @return bool TRUE if loaded successfully, FALSE otherwise.
	* @see PC_core::Get_theme_path()
	* @todo edit: function now checks if site is active, otherwise it shows default under construction template from formatted text.
	*/
	public function Load_site_data($site, $forceActive=false) {
		$this->debug("Load_site_data()");
		$this->data = $site;
		//if domain default language is not set then use sites first set language
		if (!empty($this->data['ln'])) {
			$ln = $this->data['ln'];
		}
		else {
			reset($this->data['languages']);
			if (!empty($this->data['languages'][key($this->data['languages'])])) {
				$ln = key($this->data['languages']);
			}
			else {
				$ln = 'en';
			}
		}
		$this->ln = $this->default_ln = $ln;
		//parse routes
		/*$pattern = str_replace('%', '(.*?)', preg_quote(substr($site['mask'], strpos($site['mask'], '/'))));
		preg_match("#^".$pattern."$#i", $_SERVER['REQUEST_URI'], $m);*/
		$site_mask_pos = strpos($site['mask'], '/');
		if ($forceActive and $site_mask_pos) {
			$this->debug("Site has mask", 5);
			//define request
			$new_request = $request =& $_SERVER['REQUEST_URI'];
			$this->debug("new request is: " . $new_request, 5);
			$pos = strpos($request, '?');
			if ($pos) $new_request = $request = substr($request, 0, $pos);
			$this->debug("new request is: " . $new_request, 5);
			//define entry request
			$pattern = str_replace('%', '', substr($site['mask'], $site_mask_pos));
			$old_base_url = preg_replace('#^https?://#ui', '', $this->cfg['url']['base']);
			$old_base_url = substr($old_base_url, strpos($old_base_url, '/'));
			
			$pre_request = '';
			$mask_pos = strpos($request, $pattern);
			$this->debug("Mask pos:" . $mask_pos , 4);
			if ($mask_pos) {
				$pre_request = mb_substr($request, 0, $mask_pos);
				$this->debug("pre_request: " . $pre_request , 4);
				//$pre_request = ltrim($pre_request, '/');
			}
			
			$this->link_prefix = substr($pre_request . $pattern, mb_strlen($old_base_url));
			$this->debug("link_prefix = substr($pre_request . $pattern, mb_strlen($old_base_url));", 4);
			$this->debug("Link prefix: " . $this->link_prefix, 5);
			
			$new_request = mb_substr($request, mb_strlen($pattern) + $mask_pos);
			$this->debug("mb_substr($request, mb_strlen($pattern) + $mask_pos)", 5);
			$this->debug("So, new request will be: " . $new_request, 6);
			$this->routes->Parse_request($new_request);
		}
		//check if site theme exists
		$this->data['tpl'] = $this->core->Get_theme_path(null, false).'template.php';
		if (!is_file($this->data['tpl'])) {
			$this->core->Show_error('theme_not_found');
			return false;
		}
		$this->core->Init_hooks('core/site/headers', array());
		$this->_output_headers();
		//add default scripts & styles to queue
		$this->Add_script('media/jquery-1.8.0.min.js', 100);
		$this->Add_script('media/swfobject.js', 80);
		$this->Add_script('media/jquery.prettyPhoto.js');
		$this->Add_script('media/cms.js', -1);
		$this->Add_stylesheet('media/css/prettyPhoto.css');
		$this->Add_stylesheet('themes/'.$this->data['theme'].'/custom.css');
		//check if site is active
		if ($forceActive) if (!$site['active']) {
			$activate_site = false;
			$strictly = false;
			$this->core->Init_hooks('site_activity_additional_check', array(
				'activate_site'=> &$activate_site,
				'strictly'=> &$strictly
			));
			if (!$strictly) {
				if (v($this->route[1]) == 'new') {
					//administrators should see under construction sites
					if (!$this->auth->Is_authenticated()) $this->auth->Authenticate();
					if ($this->auth->Authorize('core', 'access_admin')) {
						$activate_site = true;
						$this->routes->Shift();
						$this->link_prefix .= 'new/';
					}
				}
			}
			if (!$activate_site) {
				$tpl = $this->Get_theme_path().'PC_template_under_construction.php';
				$r = $this->page->Get_by_controller('core/inactive');
				$p = false;
				if (count($r)) {
					$p = $this->page->Get_page($r[0]);
					if ($p) $this->Load_page($p);
				}
				//show under construction page generated by themes` template
				if (is_file($tpl)) require($tpl);
				//generate without template
				else if ($p) {
					echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
						.'<html xmlns="http://www.w3.org/1999/xhtml" style="height:100%;">'
						.'<head>'
						.'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'
						.'<title>'.$this->Get_title().'</title>'
						.'<base href="'.htmlspecialchars($this->cfg['url']['base']).'" />'
						.$this->Get_seo_html()
						.$this->Get_stylesheets_html()
						.$this->Get_scripts_html()
						.$this->Get_favicon()
						.(!empty($p['info'])?'<style type="text/css">'.html_entity_decode(strip_tags($p['info'])).'</style>':'')
					.'</head><body style="height:100%;padding:0;margin:0;"><table class="pc_content" width="100%" height="100%"><tr valign="middle"><td width="30%">&nbsp;</td><td class="pc_content">';
					echo $p['text'].'</td><td width="30%">&nbsp;</td></tr></table></body></html>';
				}
				else echo 'Website is turned off.';
				return false;
			}
		}
		return true;
	}
	/**
	* Method used to render site. In this method called PC_routes::Get_request() method for getting current request. Also called 
	* PC_site::Load_page_by_path(), PC_site::Add_script(), PC_site::Add_stylesheet().
	* @return bool TRUE if rendering was successful, FALSE otherwise.
	* @see PC_routes::Get_request()
	* @see PC_site::Load_page_by_path()
	* @see PC_site::Add_script()
	* @see PC_site::Add_stylesheet()
	*/
	public function Render() {
		//check link validity
		$request = $this->routes->Get_request();
		if (!v($this->cfg['router']['no_trailing_slash']) and (!empty($request) && substr($request, -1) != '/')) {
			$url = $this->cfg['url']['base'];
			$url .= $request."/";
			if (!empty($this->routes->get_request)) {
				$url .= '?'.$this->routes->get_request;
			}
			$this->core->Redirect($url, 301);
		}
		elseif (v($this->cfg['router']['no_trailing_slash']) and (!empty($request) && substr($request, -1) == '/')) {
			$url = $this->cfg['url']['base'];
			$url .= substr($request, 0, -1);
			if (!empty($this->routes->get_request)) {
				$url .= '?'.$this->routes->get_request;
			}
			$this->core->Redirect($url, 301);
		}
		elseif (substr($_SERVER['REQUEST_URI'], -2) == '//') {
			$this->core->Show_error(404);
			return false;
		}
		else {
			//select user prefered language
			// 1. by first route
			if (isset($this->route[1]) && isset($this->data['languages'][$this->route[1]])) {
				$this->ln = $this->route[1];
				$this->routes->Shift();
			}
			else $this->ln = $this->default_ln;
			
			if (v($this->route[1]) == $this->default_ln) {
				$this->core->Show_error(404);
				return false;
			}
			else {
				//new page load mode
				$route = $this->Load_page_by_path();
				//select page (check route)
				//if (isset($this->route[$this->route_shift])) {
				/*if ($this->routes->Exists(1)) {
					$route = $this->Load_page_by_route($this->route[1], false, array(), false);
				}
				//detect home page route
				else $route = $this->Load_page_by_route(null, false, array(), false);*/
				if ($route) {
					$this->render = $this->plugins->controllers->Execute();
					
					$this->core->Init_hooks('after_render', array(
						'rendered'=> $this->render,
						'page'=> $route
					));
					
					return $this->render;
				}
			}
		}
	}
	/**
	* Method used to load site by given site id. In this method called PC_site::Get() method for getting site data. Also called PC_site::Load_site_data().
	* @see PC_site::Get() 
	* @see PC_site::Load_site_data()
	*/
	public function Load($id) {
		$d = $this->Get($id);
		$this->Load_site_data($d);		
	}	
	/**
	* Method used to retrieve site data by request (or by optional $entry_address) from database.
	* @param mixed $entry_address given to get site data for.
	* @return mixed array containing data about site.
	*/
	public function Get_by_domain($entry_address=null) {
		$this->debug("Get_by_domain($entry_address)");
		global $cfg;
		if (is_null($entry_address)) {
			$request =& $_SERVER['REQUEST_URI'];
			$this->debug("Request is: " . $request, 1);
			$pos = strpos($request, '?');
			//$entry_address = rtrim($_SERVER['HTTP_HOST'].($pos?substr($request, 0, $pos):$request), '/');
			$entry_address = $_SERVER['HTTP_HOST'].($pos?substr($request, 0, $pos):$request);
			//$entry_address = preg_replace("#^http://(.+)$#", "$1", $cfg['url']['base']);
		}
		$this->debug("Entry address is: " . $entry_address, 1);
		$query = "SELECT s.id,s.name,s.theme,d.ln,".$this->sql_parser->group_concat($this->sql_parser->concat_ws("░", 'l.nr', 'l.ln', 'l.name', 'l.disabled', 'l.name'), array('order'=>array('by'=>'l.nr'),'separator'=>'▓'))." languages, mask, active"
			." FROM {$this->db_prefix}domains d"
			." LEFT JOIN {$this->db_prefix}sites s ON id=d.site"
			." LEFT JOIN {$this->db_prefix}languages l ON l.site=s.id AND l.disabled = 0"
			." WHERE ? LIKE mask GROUP BY s.id,s.name,s.theme,d.ln,d.nr,d.mask ORDER BY d.nr,length(mask) desc LIMIT 1";
		$r = $this->prepare($query);
		$query_params = array($entry_address);
		$this->debug_query($query, $query_params, 2);
		$s = $r->execute($query_params);
		if (!$s) die('Get_site_by_domain error.');
		if ($r->rowCount() < 1) return false;
		$d = $r->fetch();
		$d['languages'] = explode('▓', $d['languages']);
		$all = array();
		foreach ($d['languages'] as &$language) {
			$language = explode('░', $language);
			$all[$language[1]] = $language[2];
		}
		$d['languages'] = $all;
		return $d;
	}
	/**
	* Method used to get site by given site id. Also called PC_site::Get_all().
	* @param mixed $id given to look for the site.
	* @param bool $cache given to indicate of cache usage; TRUE means that cache is used.
	* @param bool $get_all_simultaneously given to indicate, that all sites loaded to cache if specified TRUE.
	* @return mixed array containing data about requested site.
	* @see PC_memstore::Get()
	* @see PC_site::Get_all()
	*/
	public function &Get($id, $cache=true, $get_all_simultaneously=false) {
		$this->debug("Get()");
		if ($cache) {
			$cached =& $this->memstore->Get('sites', $id);
			if ($cached) return $cached;
		}
		if ($get_all_simultaneously) {
			//load all sites to cache
			$this->Get_all();
			//cache is filled, so we can Get one requested from cache ($cache argument must be true - it is by default, so we only define one arg $id)
			return $this->Get($id);
		}
		//get from database and cache it
		$r = $this->prepare("SELECT s.*,d.mask,d.ln,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws("░",'l.ln','l.name'), array('order'=>array('by'=>'l.nr'),'separator'=>'▓'))." langs"
		." FROM {$this->db_prefix}sites s"
		." LEFT JOIN {$this->db_prefix}languages l ON l.site=s.id AND l.disabled = 0"
		." LEFT JOIN {$this->db_prefix}domains d ON d.site=s.id"
		." WHERE s.id=? GROUP BY s.id,s.name,s.theme,s.editor_width,s.editor_background,d.mask,d.nr,d.ln ORDER BY s.id LIMIT 1");
		$success = $r->execute(array($id));
		if (!$success) {
			$this->memstore->Uncache('sites', $id);
			return false;
		}
		$site = $r->fetch();
		$langs = explode('▓', $site['langs']);
		$site['languages'] = array();
		foreach ($langs as &$lang) {
			$lang = explode('░', $lang);
			$site['languages'][$lang[0]] = $lang[1];
		}
		return $this->memstore->Cache(array('sites', $id), $site);
	}
	/**
	* Method used to get all sites.
	* @param bool $cache given to indicate of cache usage; TRUE means that cache is used.
	* @return mixed array of cached sites.
	* @see PC_memstore::Get()
	*/
	public function Get_all($cache=true) {
		$this->debug("Get_all()");
		if ($cache && $this->memstore->Is_cached('sites')) {
			return $this->memstore->Get('sites');
		}
		$sites = array();
		$r = $this->query("SELECT s.*,d.mask,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws("░",'l.ln','l.name', 'l.disabled'), array('order'=>array('by'=>'l.nr'),'separator'=>'▓'))." langs"
		." FROM {$this->db_prefix}sites s"
		." LEFT JOIN {$this->db_prefix}languages l ON l.site=s.id"
		." LEFT JOIN {$this->db_prefix}domains d ON d.site=s.id"
		." GROUP BY s.id,s.name,s.theme,s.editor_width,s.editor_background,d.mask,d.nr ORDER BY s.id, d.nr");
		if (!$r) {
			$this->memstore->Uncache('sites');
			return false;
		}
		while ($site = $r->fetch()) {
			if (!empty($site['langs'])) {
				$langs = explode('▓', $site['langs']);
				$site['langs'] = array();
				foreach ($langs as &$lang) {
					$lang = explode('░', $lang);
					//$site['langs'][$lang[0]] = $lang[1];
					$site['langs'][$lang[0]] = array($lang[1], $lang[2]);
				}
				unset($langs);
			} else $site['langs'] = array();
			$sites[$site['id']] = $site;
		}
		return $this->memstore->Cache('sites', $sites);
	}
	//routes
	/**
	* Method used to get seach page from database.
	* @return mixed FALSE if page is not loaded, search page route otherwise.
	*/
	public function Get_search_page_route() {
		if (!$this->Is_loaded()) return false;
		$r = $this->prepare("SELECT route FROM {$this->db_prefix}pages p LEFT JOIN {$this->db_prefix}content c ON pid=p.id WHERE controller='search' AND ln=? LIMIT 1");
		$success = $r->execute(array($this->ln));
		if ($success) {
			return $r->fetchColumn();
		}
	}
	/**
	* Method used to append given values on instance variable loaded_page['path'].
	* @return bool TRUE.
	*/
	public function Path_append() {
		//first value = plugin for whom this path item belongs
		$args = func_get_args();
		$pluginName = array_shift($args);
		foreach ($args as $item) {
			if (!is_array($this->loaded_page['subpath'])) $this->loaded_page['subpath'] = array();
			$item['_correspondingPlugin'] = $pluginName;
			$this->loaded_page['subpath'][] = $item;
		}
		return true;
	}
	
	public function Get_controller_text($controller) {
		if (!$this->plugins->controllers->Is_active($controller)) {
			return false;
		}
		require_once $this->core->Get_path('plugins', '', $controller) . 'PC_controller.php';
		$controller_object = $this->core->Get_object('PC_controller_poll', array(true));
		if (!$controller_object) {
			return false;
		}
		$controller_object->Process(false);
		return $controller_object->text;
	}
	
	public function Get_widget_text($widget_name) {
		$args = func_get_args();
		array_shift($args);
		$widget = $this->core->Get_object($widget_name, $args);
		return $widget->get_text();
	}
	
	public function Get_widget_text_from_data($widget_name, $data) {
		$args = func_get_args();
		array_shift($args);
		$data = array_shift($args);
		$widget = $this->core->Get_object($widget_name, $args);
		return $widget->get_text($data);
	}
	
	public function Get_widget_data($widget_name) {
		$args = func_get_args();
		array_shift($args);
		$widget = $this->core->Get_object($widget_name, $args);
		return $widget->get_data();
	}
	
	//loaded page
	/**
	* Method used to retrieve page text by given page id. In this method is called PC_site::Get_text() method.
	* @param int $id given page id to look text for.
	* @return string text of the queried page.
	* @see PC_site::Get_text()
	• @todo rewrite comment - second parameter was added
	*/
	public function Get_text($id=null, $force_headings=true) {
		$this->text_without_headings = $this->text;
		if ($id > 0 and $id == $this->page->get_id() and !empty($this->text_without_headings)) {
			$id = null;
		}
		if (isset($this->force_headings)) $force_headings = $this->force_headings;
		if ($id < 1) {
			$text = $this->text_without_headings;
		}
		else {
			$text = $this->page->Get_text($id);
		}
		$pre_text = '';
		if ($force_headings) {
			$h1_name = $this->loaded_page['custom_name'];
			if (empty($h1_name)) {
				$h1_name = $this->loaded_page['name'];
			}
			if (!preg_match("#^\s*<h1[^>]*>#", $text)) if (!empty($h1_name)) {
				$pre_text = "<h1>".$h1_name."</h1>\n";
			}
		}
		//$this->text_without_headings = $text;
		//$this->text = $pre_text . $text;
		return $pre_text . $text;
	}
	
	public function Get_title_for_text() {
		$title = v($this->loaded_page['custom_name']);
		if (empty($title)) {
			$title = v($this->loaded_page['name']);
		}
		return $title;
	}
	
	public function Get_title_for_text_from_page_data(&$page_data) {
		$title = v($page_data['custom_name']);
		if (empty($title)) {
			$title = v($page_data['name']);
		}
		return $title;
	}
	
	/**
	* @todo remove or use this method... At 2012-01-20 14:23 method is unused and inside calling undefined methods...
	*/
	public function Render_content_template() {
		$this->Start_output();
		//require($this->Get_plugin_path()."PC_template_default.php");
		$this->End_output($this->text);
	}
	
	public function Render_template($template, $vars = array()) {
		foreach ($vars as $key => $value) {
			$$key = $value;
		}
		//$this->Start_output();
		
		$full_path = $template;
		if (strpos($full_path, CMS_ROOT) === false) {
			$full_path = CMS_ROOT . $template;
		}
		include $full_path;
		//require($this->Get_plugin_path()."PC_template_default.php");
		//$this->End_output($this->text);
	}
	
	public function Get_template_content($template, $vars = array()) {
		$this->Output_start();
		$this->Render_template($template, $vars);
		$s = '';
		$this->Output_end($s);
		return $s;
	}
	
	public function Include_tpl($group, $template, $vars = array()) {
		
	}
	
	public function Get_tpl_content($group, $template = 'tpl', $vars = array()) {
		if (is_array($template)) {
			$vars = $template;
			$template = 'tpl';
		}
		$vars['tpl_group'] = $group;
		return $this->Get_template_content($this->core->Get_tpl_path($group, $template), $vars) ;
	}
	
	/**
	* Method used to retrieve loaded page title.
	* @return string title of the loaded page.
	*/
	public function Get_title() {
		if (!$this->Page_is_loaded()) return false;
		$title = str_replace('"', '&quot;', (!empty($this->loaded_page['title'])?$this->loaded_page['title']:$this->loaded_page['name']));
		$title = strip_tags($title);
		return $title;
	}
	/**
	* Method used to set loaded page title.
	* @return bool TRUE.
	*/
	public function Set_title($title) {
		$this->loaded_page['title'] = $title;
		return true;
	}
	/**
	* Method used to get SEO of loaded page.
	* @return string.
	*/
	public function Get_seo_html() {
		if (!$this->Page_is_loaded()) return false;
		$list = array();
		$this->core->Init_hooks('core/site/get-seo-html', array(
			'list'=> &$list
		));
		
		$list[] = '<meta name="keywords" content="'.v($this->loaded_page['keywords']).'" />';
		$list[] = '<meta name="description" content="'.v($this->loaded_page['description']).'" />';
		if (v($this->cfg['meta_author']) and !empty($this->cfg['meta_author'])) {
			$list[] = '<meta name="author" content="'. $this->cfg['meta_author'] .'" />';
		}
		return implode("\n", $list) . "\n";
	}
	/**
	* Method used to get favicon HTML markup
	* @return string HTML markup if theme has one, FALSE otherwise.
	*/
	public function Get_favicon($theme=null) {
		$path = $this->Get_theme_path($theme, false);
		if (!$path) return false;

		$publicPath = '';
		if (is_file($path."favicon.ico")) {
			$publicPath = $this->Get_theme_path($theme)."favicon.ico";
		}
		if (empty($publicPath)) {
			$default_icon_path = 'media/images/favicon.ico';
			if (is_file($default_icon_path)) {
				$publicPath = $default_icon_path;
			}
		}
		
		if (!empty($publicPath)) {
			return '<link rel="icon" href="'.$publicPath.'" type="image/vnd.microsoft.icon" />'
			.'<link rel="shortcut icon" href="'.$publicPath.'" type="image/vnd.microsoft.icon" />';
		}
		
		return false;
	}
	/**
	* Method used check if page is open.
	* @return bool TRUE if page is open, FALSE otherwise.
	*/
	public function Is_opened($id, $idKey='pid', $pluginName=null) {
		if (is_null($pluginName)) {
			if (!is_array(v($this->loaded_page['route_path']))) return false;
			foreach ($this->loaded_page['route_path'] as $i) {
				if (v($i['pid']) == $id) {
					return true;
				}
			}
		}
		else {
			if (!is_array(v($this->loaded_page['subpath']))) return false;
			foreach ($this->loaded_page['subpath'] as $i) {
				if (v($i['_correspondingPlugin']) != $pluginName) continue;
				if (v($i[$idKey]) == $id) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	* Method used get page path. Inside method is called method PC_page::Get_path()
	* @param mixed $page given to get path for.
	* @param bool $cache given to indicate if use a cache; TRUE means that cache is used.
	* @return mixed page path on success, FALSE otherwise
	* @see PC_page::Get_path()
	*/
	public function Get_page_path($page=null, $cache=true) {
		//null value of $page means that we should return currently loaded page path
		if (is_null($page)) {
			if ($this->Page_is_loaded()) {
				$path = $this->loaded_page['path'];
			}
			else return false;
		}
		else $path = $this->page->Get_path($page, $cache);
		if (is_array(v($this->loaded_page['subpath']))) {
			$path = array_merge($path, $this->loaded_page['subpath']);
		}
		return $path;
	}
	/**
	* Method used to check if loaded page is "front" or home page.
	* @return bool TRUE if loaded page is considered front, FALSE otherwise.
	*/
	public function Is_front_page() {
		if (v($this->loaded_page['front'])) return true;
		elseif (v($this->loaded_page['route_path'][0]['front'])) return true;
		elseif (v($this->loaded_page['route_path'][0]['redirect_from_home'])) return true;
		return false;
	}
	/**
	* Method used to check if loaded page is "search" or home page.
	* @return bool TRUE if loaded page is considered search page, FALSE otherwise.
	*/
	public function Is_search_page() {
		return (v($this->loaded_page['controller']) == 'search');
	}
	/**
	* @todo implement or to remove this method. It's left blank.
	*/
	public function Get_next_page() {
		if (!isset($this->loaded_page)) return false;
		if ($this->Is_front_page()) {
			//query for first page which is not menu
			//or Get_menu(nr) and shift first page
		}
		else {
			//get page nr and idp (parent) from loaded_page[path] and query for next page following that nr
			/*$r = $db->prepare("SELECT route,front"
			." FROM {$cfg['db']['prefix']}pages p"
			." JOIN {$cfg['db']['prefix']}content c on c.pid=p.id and c.ln=?"
			." WHERE idp=? and controller!= 'menu' and deleted=0 and published=1"
			." and nr>? order by nr"
			." limit 1");*/
		}
		//from BMG project
		/*
		if ($site->Is_front_page()) {
			$st_menu = $page->Get_menu(1);
			foreach ($st_menu as &$item) {
				if ($item['pid'] != 5) {
					$next_page_route = $item['route'];
					break;
				}
			}
		}
		else {
			$parent = array_shift($site->loaded_page['path']);
			//print_pre($parent);
			$params = array($site->ln, $parent['idp'], $parent['nr']);
			$r = $db->prepare("SELECT route,front"
			." FROM {$cfg['db']['prefix']}pages p"
			." JOIN {$cfg['db']['prefix']}content c on c.pid=p.id and c.ln=?"
			." WHERE idp=? and controller!= 'menu' and deleted=0 and published=1"
			." and nr>? order by nr"
			." limit 1");
			$success = $r->execute($params);
			if ($success && $r->rowCount()) {
				$d = $r->fetch();
				if ($d['front']) $next_page_route = '';
				else $next_page_route = $d['route'];
			}
		}
		if (empty($next_page_route)) $next_page_route = $cfg['url']['base'];
		else $next_page_route = $site->Get_link($next_page_route);
		*/
	}
	/**
	* @todo implement or to remove this method. It's left blank.
	*/
	public function Get_previous_page() {
		
	}
	/**
	* Method used to get page path by level.
	* @param int $level given to specify loaded page path level.
	* @return mixed loaded page level.
	*/
	public function Get_page_by_level($level) {
		if (isset($this->loaded_page['path'][$level-1]))
			return $this->loaded_page['path'][$level-1];
	}
	/**
	* Method used to get site theme path. In this method is called PC_core::Get_theme_path().
	* @return mixed theme path.
	* @see PC_core::Get_theme_path()
	*/
	public function Get_theme_path() {
		return $this->core->Get_theme_path();
	}
	/**
	* Method used to get site theme directory.
	* @return mixed theme directory.
	*/
	public function Get_theme_dir() {
		if (isset($this->site->data['theme'])) return $this->site->data['theme'];
	}
	/**
	* Method used to get loaded page controllers.
	* @return mixed array of controllers.
	*/
	public function Get_parent_controllers() {
		$ctrls = array();
		foreach ($this->loaded_page['path'] as $p) {
			if (!empty($p['controller'])) $ctrls[] = $p['controller'];
		}
		return $ctrls;
	}
	/**
	* Method used to get last of loaded page controllers. This method calls PC_site::Get_parent_controllers()
	* @return mixed last controller in loaded page.
	* @see PC_site::Get_parent_controllers()
	*/
	public function Get_last_controller() {
		$ctrls = $this->Get_parent_controllers();
		if (!count($ctrls)) return false;
		return array_pop($ctrls);
	}
	/**
	* Method used to get first of loaded page controllers. This method calls PC_site::Get_parent_controllers()
	* @return mixed last controller in loaded page.
	* @see PC_site::Get_parent_controllers()
	*/
	public function Get_first_controller() {
		$ctrls = $this->Get_parent_controllers();
		if (!count($ctrls)) return false;
		return array_shift($ctrls);
	}
	/**
	* Method used to check if loaded page has controllers. This method calls PC_site::Get_parent_controllers()
	* @param mixed $ctrl given to check for.
	* @return bool TRUE if controller found, FALSE otherwise.
	* @see PC_site::Get_parent_controllers()
	*/
	public function Has_controller($ctrl) {
		$ctrls = $this->Get_parent_controllers();
		if (!count($ctrls)) return false;
		return in_array($ctrl, $ctrls);
	}
	//languages
	/**
	* Method used to get installed languages. Method calls PC_site::Is_loaded()
	* @return mixed array of installed languages.
	* @see PC_site::Is_loaded()
	*/
	public function Get_languages() {
		if ($this->Is_loaded()) {
			return $this->data['languages'];
		}
	}
	
	
	public function Get_all_languages() {
		$lang_model = $this->core->Get_object('PC_language_model');
		$lang_model->absorb_debug_settings($this);
		return $lang_model->get_all(array(
			'where' => array('NOT disabled'),
			'key' => 'ln',
			'order' => 'site, nr'
		));
	}
	
	/**
	* Method used to get HTML markup in unordered list notation with installed languages.
	* @return mixed array of installed languages.
	* @see PC_site::Get_languages()
	* @see PC_site::Get_link()
	*/
	public function Get_html_languages($params = array()) {
		$this->debug("Get_html_languages()");
		$lns = $this->Get_languages();
		if (!$lns) return false;
		$ul_class_full = '';
		if (isset($params['ul_class'])) {
			$ul_class_full = ' class = "'.$params['ul_class'].'"';
		}
		$html = '<ul'.$ul_class_full.'>';
		global $cfg;
		foreach ($lns as $code=>$ln) {
			$class_full = '';
			if ($code == $this->ln) {
				$class_full = ' class = "'.v($params['li_active_class'], 'active').'"';
			}
			//$html .= '<li><a href="'.($code == $this->data['ln']?$cfg['url']['base']:$code.'/');
			$html .= '<li'.$class_full.'><a href="'.$this->Get_link(null, $code);
			/*if (isset($this->loaded_page['routes'][$code])) {
				$html .= $this->loaded_page['routes'][$code].'/';
			}*/
			$html .= '">'.$ln.'</a></li>';
		}
		$html .= '</ul>';
		return $html;
	}
	/**
	* Method used to set current language.
	* @param mixed $ln given language to set.
	* @return bool TRUE if language set, FALSE otherwise.
	*/
	public function Set_language($ln) {
		if (!isset($this->data)) return false;
		if (!isset($this->data['languages'][$ln])) return false;
		$this->ln = $ln;
		return true;
	}
	//links
	/**
	* Method used to get link prefix.
	* @param mixed $ln given language to conbined with prefix.
	* @param bool given to indicate if slash should be used in combining prefix.
	* @param  bool given to indicate if add instance variable $link_prefix before prefix.
	* @return bool TRUE if language set, FALSE otherwise.
	* @see PC_site::Language_exists()
	* @see PC_site::Is_default_language()
	*/
	public function Get_link_prefix($ln=null, $slash_suffix=true, $prepend_prefix=true) {
		$prefix = ($prepend_prefix?$this->link_prefix:'');
		if (isset($ln) && $this->Language_exists($ln)) {
			if (!$this->Is_default_language($ln)) {
				//specified language
				$prefix .= $ln.($slash_suffix?'/':'');
			}
		}
		else {
			//current language
			if (!$this->Is_default_language($this->ln)) {
				$prefix .= $this->ln.($slash_suffix?'/':'');
			}
		}
		return $prefix;
	}
	public function Get_home_link() {
		$args = func_get_args();
		return $this->cfg['url']['base'].call_user_func_array(array($this, 'Get_link_prefix'), $args);
	}
	public function Get_link_by_controller($controller_id) {
		global $page;
		$page_id = $page->Get_by_controller($controller_id);
		if( empty($page_id) ) return null;
		$page_data = $page->Get_page($page_id[0]);
		if( empty($page_data) ) return null;
		return $this->Get_link($page_data['route']);
	}
	/**
	* Method used to get link.
	* @param mixed $route given to combine link from.
	* @param mixed $ln given to combine language with link as well.
	* @param  bool $prepend_prefix given to indicate if use instance variable $link_prefix.
	* @return mixed link
	* @see PC_site::Get_link_prefix()
	* @see PC_site::Is_front_page()
	*/
	public function Get_link($route=null, $ln=null, $prepend_prefix=true, $suffix='') {
		$this->debug("Get_link($route, $ln)");
		if (empty($ln)) $ln = $this->ln;
		//get language prefix
		$ln_domain = '';
		if (false and is_null($route) and $this->ln != $ln) {
			$this->debug('ln differs!', 1);
			$cache_key = 'ln_domain_' . $ln;
			$ln_domain = $this->memstore->Get($cache_key);
			if ($ln_domain === false) {
				$domain_model = $this->core->Get_object('PC_domain_model');
				$domain_model->absorb_debug_settings($this, 1);
				$ln_domain_data = $domain_model->get_all(array(
					'select' => 'mask',
					'where' => array(
						'site' => $this->get_id(),
						'ln' => $ln
					),
					'order' => 'nr ASC',
					'limit' => 1
				)); 
				if ($ln_domain_data) {
					$ln_domain = trim($ln_domain_data['mask'], '%');
				}
				else {
					$ln_domain = '';
				}
				$this->memstore->Cache($cache_key, $ln_domain);
			}
			$this->debug($ln_domain, 2);
		}
		$pre_ln = $ln;
		if (!empty($ln_domain)) {
			$pre_ln = '';
			$ln_domain = 'http://' . $ln_domain;
		}
		$link = $this->Get_link_prefix($pre_ln, true, $prepend_prefix);
		$this->debug('link after prefix: ' . $link, 1);
		//append route
		if (!empty($route)) {
			$link .= $route;
		}
		//sekantis ifas atkomentuotas todel, kad sis funkcionalumas reikalingas sudarinejant puslapio kalbos pasirinkimo linkus
		elseif (!$this->Is_front_page()) {
			$link_to_append = v($this->loaded_page['routes'][$ln]);
			if (v($this->loaded_page['permalinks'][$ln])) {
				$link_to_append = $this->loaded_page['permalinks'][$ln];
			}
			$link .= $link_to_append;
			$this->debug('link after routes+permalinks: ' . $link, 1);
		}
		elseif (empty($link)) {
			$link = $this->cfg['url']['base'].$this->link_prefix;
			$this->debug('link after base: ' . $link, 1);
		}
		if (strrpos($link, '?')) {
			if (substr($link, (strrpos($link, '?')-1), 1) != '/') {
				$link = substr($link, 0, strrpos($link, '?')) . $this->cfg['trailing_slash'] . substr($link, strrpos($link, '?'));
			}
		}
		else if (substr($link, -1) != '/') $link .= $this->cfg['trailing_slash'];
		
		$this->debug('link after ?: ' . $link, 1);
		
		if (is_null($route) ) {
			if (isset($this->url_suffix_callback_object) and is_object($this->url_suffix_callback_object) and method_exists($this->url_suffix_callback_object, $this->url_suffix_callback_method)) {
				$args = $this->url_suffix_callback_args;
				$args[] = $ln;
				//echo '<hr />';
				$suffix_from_callback = call_user_func_array(array($this->url_suffix_callback_object, $this->url_suffix_callback_method), $args);
				$link = pc_append_route($link, $suffix_from_callback);
				$this->debug('link after suffix callback: ' . $link, 1);
			}
		}
		$link = $ln_domain . pc_append_route($link, $suffix);
		$this->debug($link, 5);
		return $link;
	}
	
	public function Set_url_suffix_callback($object, $method, $args = array()) {
		$this->url_suffix_callback_object = $object;
		$this->url_suffix_callback_method = $method;
		$this->url_suffix_callback_args = $args;
	}
	
	/**
	* Method used to get current link. In this method used PC_routes::Get_request();
	* @param string $suffix given to append at end of link.
	* @param  bool $include_get_data.	
	* @return mixed link
	* @see PC_routes::Get_request()
	*/
	public function Get_current_link($suffix='', $include_get_data=false) {
		return $this->routes->Get_request().$suffix;
	}
	/**
	* Method used to check if given language is installed.
	* @param mixed $ln language given to look for.
	* @return mixed TRUE if given language is installed, nothing otherwise.
	*/
	public function Language_exists($ln) {
		if (isset($this->data['languages'][$ln])) return true;
	}
	/**
	* Method used to check if given language is default.
	* @param mixed $ln language given to check for.
	* @return mixed TRUE if given language is default, FALSE otherwise.
	*/
	public function Is_default_language($ln) {
		return ($ln == $this->default_ln);
	}
	public function Is_current_language($ln) {
		return ($ln == $this->ln);
	}
	//Move to PC_media class
	//stylesheets
	/**
	* Method used to add stylesheet file to styles collection.
	* @param mixed $src file source path given to add to styles collection.
	* @param int $priority importance given of current stylesheet.
	* @return bool TRUE if addition was successfull, FALSE if $src given empty.
	*/
	public function Add_stylesheet($src, $priority=1) {
		if (empty($src)) return false;
		$this->_stylesheets[$src] = intval($priority);
		return true;
	}
	/**
	* Method used to remove stylesheet file from styles collection.
	* @param mixed $src file source path given to remove from styles collection.
	* @return bool TRUE if removal was successfull, FALSE if $src given empty.
	*/
	public function Remove_stylesheet($src) {
		if (empty($src)) return false;
		unset($this->_stylesheets[$src]);
		return true;
	}
	/**
	* Method used to get stylesheets collection.
	* @param bool $sort given indicate if sort styles.
	* @return mixed array of sylesheets.
	*/
	public function Get_stylesheets($sort=false) {
		if ($sort) arsort($this->_stylesheets);
		return $this->_stylesheets;
	}
	/**
	* Method used to get HTML markup with stylesheets.
	* @return string stylesheets HTML markup.
	*/
	public function Get_stylesheets_html($sort = true) {
		if ($sort) {
			arsort($this->_stylesheets);
		}
		if (!count($this->_stylesheets)) return false;
		$html = '';
		foreach ($this->_stylesheets as $sheet=>$priority)
		$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sheet\" />\n";
		return $html;
	}
	//scripts
	/**
	* Method used to add script file to scripts collection.
	* @param mixed $src file source path given to add to scripts collection.
	* @param int $priority importance given of current script.
	* @return bool TRUE if addition was successfull, FALSE if $src given empty.
	*/
	public function Add_script($src, $priority=1) {
		if (empty($src)) return false;
		$this->_scripts[$src] = intval($priority);
		return true;
	}
	
	public function Add_header($header, $value) {
		if (empty($value)) return false;
		$this->_headers[$header] = $value;
	}
	
	/**
	* Method used to remove script file from scripts collection.
	* @param mixed $src file source path given to remove from scripts collection.
	* @return bool TRUE if removal was successfull, FALSE if $src given empty.
	*/
	public function Remove_script($src) {
		if (empty($src)) return false;
		unset($this->_scripts[$src]);
		return true;
	}
	/**
	* Method used to get scripts collection.
	* @param bool $sort given indicate if sort scripts.
	* @return mixed array of scripts.
	*/
	public function Get_scripts($sort=false) {
		if ($sort) arsort($this->_scripts);
		return $this->_scripts;
	}
	/**
	* Method used to get HTML markup with scripts.
	* @return string scripts HTML markup.
	*/
	public function Get_scripts_html() {
		arsort($this->_scripts);
		if (!count($this->_scripts)) return false;
		$html = '';
		$html .= "<script type=\"text/javascript\">
			var PC_base_url = '".$this->Get_home_link()."';
		</script>\n";
		foreach ($this->_scripts as $script=>$priority)
		$html .= "<script type=\"text/javascript\" src=\"$script\"></script>\n";
		return $html;
	}
	
	public function output_headers() {
		$this->_output_headers();
	}
	
	protected function _output_headers() {
		foreach ($this->_headers as $key => $value) {
			header("$key: " . $value);
		}
	}
	
	/**
	* Method used to add some custom data cache. This method uses PC_memstore::Cache() method.
	* param mixed $key given key to add to cache with given $data.
	* param mixed $data given data to to cache.
	* @return mixed cached object.
	* @see PC_memstore::Cache()
	*/
	public function &Register_data($key, $data) {
		return $this->memstore->Cache(array('site_data', $key), $data);
	}
	/**
	* Method used to get data from cache. This method uses PC_memstore::Get() method.
	* param mixed $key given key to look for data by.
	* @return mixed object found by given key.
	* @see PC_memstore::Get_cached()
	*/
	public function &Get_data($key) {
		return $this->memstore->Get(array('site_data', $key));
	}
}