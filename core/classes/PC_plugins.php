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

//this class is not active yet! This structure is kept for the future plugin system updates.

/**
* Class PC_plugins used to implement plugable architecture in ProfIS CMS environment.
*/
final class PC_plugins extends PC_base {
	/**
	* Field used plugins storage.
	*/
	public $_plugins;
	/**
	* Field used controllers storage.
	*/
	public $controllers;
	public $ctrls; //already loaded controllers
	/**
	* Field used loaded plugins storage.
	*/
	public $loaded_plugins = array();
	/**
	* Field used for loaded and sorted plugins storage.
	*/
	public $sorted_plugins = array();
	/**
	* Field used to define currently loading plugin.
	*/
	private $currentlyParsing;
	/**
	* Field used for plugins renderers storage.
	*/
	private $_renderers;
	public function Init() {
		$this->controllers = new PC_controllers;
	}
	/**
	* Method simply used to get variable $currentlyParsing  value of current instance , because it is protected by accessibility level.
	* @return string $currentlyParsing.
	*/
	public function Get_currently_parsing() {
		return $this->currentlyParsing;
	}
	public function setCurrentlyParsing($plugin) {
		$this->currentlyParsing = $plugin;
		return true;
	}
	public function clearCurrentlyParsing() {
		$this->currentlyParsing = null;
		return true;
	}
	
	protected function _scan_plugin_dir($directory, $include_core = false) {
		$this->debug("_scan_plugin_dir($directory)");
		//scan plugins directory
		$dir = opendir($directory);
		if (!$dir) {
			//cms die: $this->Show_error('plugin_directory', true);
			return false;
		}
		$all_plugins = array();
		while (($f = readdir($dir)) !== false) {
			if ($f == '.' || $f == '..') continue;
			$all_plugins[] = $f;
		}
		if ($include_core) {
			$all_plugins = array_merge($all_plugins, $this->cfg['core_plugins']);
		}
		$this->debug($all_plugins, 2);
		foreach ($all_plugins as $plugin) {
			//check if plugin path is valid (is directory)
			$plugin_path = $this->path['plugins'].$plugin;
			if (!is_dir($plugin_path)) {
				$plugin_path = $this->path['core_plugins'].$plugin;
				if (!is_dir($plugin_path)) {
					continue;
				};
			}
			
			
			
			$plugin_path .= '/';
			$this->currentlyParsing = $plugin;
			//analyze plugin parts
			$has_controller = is_file($plugin_path.'PC_controller.php');
			//register plugin components
			if ($has_controller) {
				$this->controllers->Register($plugin);
			}
			//save plugin summary
			$this->list['all'][$plugin] = array(
				'has_controller'=> $has_controller
			);
		}
		$this->currentlyParsing = null;
		closedir($dir);
	}
	
	
	/**
	* Method used to search plugins directory and form currently active plugins list. Plugin is considered "active" when plugin is defined in config file as 
	* well plugin file is in plugins directory. This method also set to empty arrays this instance variables $_plugins and $controllers. As well 
	* this method calls PC_plugins::Load() method.
	* @return bool TRUE if search for plugins succeeded and FALSE otherwise.
	* @see PC_plugins::Load().
	*/
	public function Scan() {
		$this->debug('Scan()');
		//unset previously loaded plugins
		$this->list = array(
			'all'=> array(),
			'active'=> array()
		);
		$this->_scan_plugin_dir($this->path['plugins']);
		$this->_scan_plugin_dir($this->path['core_plugins'], true);
		//create list of active plugins
		$active_plugins = array_merge($this->cfg['active_plugins'], $this->cfg['core_plugins']);
		$this->debug('active plugins:', 1);
		$this->debug($active_plugins, 1);
		
		$this->debug('all plugins:', 1);
		$this->debug($this->list['all'], 1);
		
		if (count($active_plugins)) foreach ($active_plugins as &$plugin) {
			if (!$this->Exists($plugin)) {
				unset($plugin);
				continue;
			}
			//plugin exists, add its' reference to the active plugins list
			$this->list['active'][$plugin] =& $this->list['all'][$plugin];
			if ($this->list['all'][$plugin]['has_controller']) {
				$this->controllers->Set_active($plugin);
			}
		}
		//init all active plugins
		foreach ($this->list['active'] as $plugin=>$data) {
			$this->Load($plugin);
		}
		$this->loaded_plugins = $this->sorted_plugins;
		return true;
	}
	/**
	* Method is quire trivial. It only checks if given plugin is in loaded plugin array.
	* @param mixed $plugin given plugin to check for.
	* @return bool TRUE if given array is loaded and FALSE otherwise.
	*/
	public function Is_loaded($plugin) {
		return in_array($plugin, $this->loaded_plugins);
	}
	/**
	* Method for loading plugin. This method contains "require()" for the file representing given plugin. Checks for plugin activity and load 
	* stage are performed. Given plugin may require other plugins, so this method works recursively and loading other plugins, if required. Required
	* plugins are mentioned in given plugin configuration file.
	* @param mixed $plugin given for load.
	* @return bool TRUE in case of successfull plugin load and FALSE otherwise.
	*/
	public function Load($plugin) {
		$this->debug("Load($plugin)");
		$plugins =& $this;
		$core =& $this->core;
		$site =& $this->site;
		$page =& $this->page;
		$routes =& $this->routes;
		$route =& $this->routes->routes;
		
		if ($this->Is_loaded($plugin)) {
			$this->debug(":( is not loaded", 1);
			return true;
		};
		if (!$this->Is_active($plugin)) {
			$this->debug(":( is not active", 1);
			return false;
		};
		$path = $this->Get_plugin_path($plugin);
		
		//$plugin_name could be used in PC_plugin.php file scope
		$plugin_name = $plugin;
		$this->setCurrentlyParsing($plugin);
		$this->loaded_plugins[] = $plugin;
		//load plugin configuration
		$configuration_file = $path.'PC_config.php';
		if (is_file($configuration_file)) {
			require($configuration_file);
			if (isset($configuration)) {
				$cfg =& $configuration;
				if (isset($cfg['requires'])) {
					if (is_array($cfg['requires'])) {
						foreach ($cfg['requires'] as $rplugin) {
							$this->Load($rplugin);
							$this->setCurrentlyParsing($plugin);
						}
					}
				}
			}
		}
		//init plugin
		$plugin_init_file = $path.'PC_plugin.php';
		$this->debug($plugin_init_file, 3);
		//$this->site->Add_stylesheet('plugin_custom.css');
		if (is_file($plugin_init_file)) {
			require($plugin_init_file);
		}
		$this->clearCurrentlyParsing();
		$this->sorted_plugins[] = $plugin;
		return true;
	}
	/**
	* Method used to get specified plugin by given name or get all plugins or get only active plugins.
	* @param mixed $plugin given as a pattern to look for plugin in $_plugins array.
	* @param bool $active given for specifyfing the type of plugin to look for. Array $_plugins contains 'all' and 'active' plugins.
	* @param bool $cache given to specify if plugins should be reloaded before seach; by default reload does not performed.
	* @return mixed plugin or plugins if found.
	*/
	public function Get($plugin=null, $active=false, $cache=true) {
		if (!$cache) $this->Scan();
		if (!empty($plugin)) {
			//get specified plugin
			if (isset($this->list[($active?'active':'all')][$plugin])) {
				return $this->list['all'][$plugin];
			}
		}
		elseif (isset($this->list[($active?'active':'all')])) {
			//get all plugins
			return $this->list[($active?'active':'all')];
		}
	}
	/**
	* Trivial method used to check if given plugin is in $_plugins array.
	* @param mixed $plugin given plugin to check for.
	* @return bool FALSE in case if given plugin not found, TRUE otherwise.
	*/
	public function Exists($plugin) {
		if (isset($this->list['all'][$plugin])) return true;
	}
	/**
	* Method used to construct given plugin path.
	* @param mixed $plugin given plugin to construct path for.
	* @return string path of the given plugin.
	*/
	public function Get_plugin_path($plugin=null) {
		if (is_null($plugin)) $plugin = $this->Get_currently_parsing();
		$path = $this->path['core_plugins'] . $plugin.'/';
		if (is_dir($path)) {
			return $path;
		}
		return $this->path['plugins'].$plugin.'/';
	}
	/**
	* Method used to construct given plugin controller path.
	* @param mixed $plugin given plugin to construct its' controllers' path.
	* @return string path of the given plugin controller.
	*/
	public function Get_controller_path($plugin) {
		return $this->Get_plugin_path($plugin).'/PC_controller.php';
	}
	/**
	* Method used to check if given controller exists in file system and in variable $controllers. This method calls PC_plugins::Get_controller_path().
	* @param mixed $ctr given controller to check for.
	* @return bool FALSE in case if given controller in files system and $controllers variable does not match, TRUE otherwise.
	* @see PC_plugins::Get_controller_path().
	*/
	public function Controller_exists($ctr) {
		$ctr_file = $this->Get_controller_path($ctr);
		if (!is_file($ctr_file)) return false;
		if (in_array($ctr, $this->controllers->Get('all'))) return true;
	}
	/**
	* Method used to check if given plugin is in $_plugins['active'] array and is locked. In this method is called other method PC_plugins::Is_locked().
	* @param mixed $plugin given plugin to check for activity.
	* @return bool TRUE in case if given plugin is active and FALSE otherwise.
	* @see PC_plugins::Is_locked().
	*/
	public function Is_active($plugin) {
		$this->debug("Is_active($plugin)");
		if (isset($this->list['active'][$plugin]) || $this->Is_locked($plugin)) return true;
		return false;
	}
	/**
	* Method used get the type of the given plugin.
	* @param mixed $plugin given plugin to check type for.
	* @return string "core" if given plugin is in core plugins array and "custom" otherwise.
	*/
	public function Get_type($plugin) {
		return (in_array($plugin, $this->cfg['core_plugins'])?'core':'custom');
	}
	/**
	* Method used to check if given plugin is locked. Plugins considered "locked" if it is in the core plugins array.
	* @param mixed $plugin given plugin to check if is locked.
	* @return bool FALSE if given plugin is not in core plugins array, TRUE otherwise.
	*/
	public function Is_locked($plugin) {
		return in_array($plugin, $this->cfg['core_plugins']);
	}
	/**
	* Method used  to collect all plugins to more convevient way for JSON encoding.
	* @return mixed $plugins array.
	*/
	public function Get_for_output() {
		foreach ($this->list['all'] as $plugin=>$data) {
			$type = $this->Get_type($plugin);
			$active = $this->Is_active($plugin);
			$plugins[] = array($type, $plugin, ucfirst($plugin), $active, $data);
		}
		return $plugins;
	}
	/**
	* Method used  to collect all controllers to more convevient way for JSON encoding.
	* @return mixed $ctrls array.
	*/
	public function Get_controllers_for_output() {
		$ctrls = array();
		foreach ($this->controllers->Get('all') as $ctr) {
			$type = $this->Get_type($ctr);
			$active = $this->Is_active($ctr);
			$ctrls[] = array($type, $ctr, ucfirst($ctr), $active);
		}
		return $ctrls;
	}
	/**
	* Method that simply checks for regular expression match with pattern "#^[a-z0-9\-_]+$#i" on given string.
	* @param string $m given string to check it comforms pattern.
	* @return bool TRUE if macth and FALSE otherwise.
	*/
	public function Validate($m) {
		if (preg_match("#^[a-z0-9\-_]+$#i", $m)) return true;
	}
	/**
	* Method used to get all active plugins from &_plugins array which are active.
	* @return mixed an array of active plugins.
	*/
	public function Get_names_of_active() {
		return array_keys($this->list['active']);
	}
	/**
	* Method used to set plugin as active or not active in database by given plugin.
	* @param mixed $plugin given plugin to set as active.
	* @param bool $active given indication if activating or deactivating. If TRUE given - plugin 
	* set as active.
	* @return bool TRUE on succes and FALSE otherwise.
	*/
	public function Set_active($plugin, $active=true) {
		//echo 'a';
		if (!$this->Exists($plugin) || $this->Is_locked($plugin)) return false;
		//echo 'b';
		if ($this->Is_active($plugin) === $active) return true;
		//echo 'c';
		if ($active) $this->list['active'][$plugin] =& $this->list['all'][$plugin];
		else unset($this->list['active'][$plugin]);
		//echo 'd';
		$r = $this->prepare("UPDATE {$this->db_prefix}config SET value=? where ckey='active_plugins'");
		$active_plugin_names = implode(',', $this->Get_names_of_active());
		$success = $r->execute(array($active_plugin_names));
		//echo 'e';
		if ($success && $r->rowCount()) return true;
		//echo 'f';
		if ($active) unset($this->list['active'][$plugin]);
		else $this->list['active'][$plugin] =& $this->list['all'][$plugin];
	}
	/**
	* Method used to check if given plugin has given renderer.
	* @param mixed $plugin given plugin to look for all its' renderers.
	* @param mixed $renderer given renderer to look for.
	* @return bool TRUE if given plugin has given renderer and FALSE otherwise.
	*/
	public function Has_renderer($plugin, $renderer) {
		//print_pre($this->renderers);
		if (isset($this->renderers[$plugin][$renderer])) return true;
	}
	//controllers
	/**
	* Method used to get controller by given controller name.
	* @param mixed $ctr given controller to look for.
	* @return mixed a controller if exists such; and FALSE if not exist or controller definition is invalid.
	*/
	public function Get_controller($ctr) {
		if (isset($this->ctrls[$ctr])) return $this->ctrls[$ctr];
		if (!$this->Controller_exists($ctr)) return false;
		$ctr_cls = "PC_controller_$ctr";
		if (!class_exists($ctr_cls)) {
			$ctr_file = $this->Get_controller_path($ctr);
			require_once($ctr_file);
			if (!class_exists($ctr_cls)) return false;
		}
		if (!is_subclass_of($ctr_cls, 'PC_controller')) {
			return false;
		}
		$this->ctrls[$ctr] = new $ctr_cls;
		return $this->ctrls[$ctr];
	}
	/**
	* Method used to call a given controller for a given function.
	* @param mixed $ctr given controller to call function of it.
	* @param string given function name in controller to be called.
	* @return mixed method returns the reference to the object which is returned by given controller function.
	*/
	public function Call($ctr, $method) {
		$ctrl = $this->Get_controller($ctr);
		if (!$ctrl) {
			if ($ctr == 'core') die('Didn\'t you break core controller accidentally?');
			$this->core->Show_error('controller_not_found');
		}
		elseif (!method_exists($ctrl, $method)) {
			return false;
		}
		else {
			$args = array_slice(func_get_args(), 2);
			return call_user_func_array(array($ctrl, $method), $args);
		}
	}
	//renderers
	/**
	* Method used to register renderer ar runtime.
	* @param mixed $rendered given renderer to be stored in-memory.
	* @param mixed $fn given function to stored with given renderer.
	* @return bool TRUE.
	*/
	public function Register_renderer($renderer, $fn) {
		//print_pre($this->currentlyParsing);
		$this->renderers[$this->currentlyParsing][$renderer] = $fn;
		return true;
	}
	/**
	* Method used to get a renderer by given plugin and given renderer. In this method is called PC_plugins::Has_renderer().
	* @param mixed $plugin given plugin to look for all its' renderers.
	* @param mixed $renderer given renderer to look for.
	* @return mixed FALSE if a given renderer does not exist, renderer otherwise.
	* @see PC_plugins::Has_renderer()
	*/
	public function Get_renderer($plugin, $renderer) {
		if (!$this->Has_renderer($plugin, $renderer)) return false;
		return $this->renderers[$plugin][$renderer];
	}
	//Get_renderers(), Init_plugins()
	//(de)activation shortcuts
	/**
	* Method used to set given plugin active. In this method is called PC_plugins::Set_active().
	* @param mixed $plugin given plugin to activate.
	* @return bool TRUE on success, FALSE otherwise. 
	* @see PC_plugins::Set_active().
	*/
	public function Activate($plugin) {
		return $this->Set_active($plugin, true);
	}
	/**
	* Method used to set given plugin as not active. In this method is called PC_plugins::Set_active().
	* @param mixed $plugin given plugin to deactivate.
	* @return bool TRUE on success, FALSE otherwise. 
	* @see PC_plugins::Set_active().
	*/
	public function Deactivate($plugin) {
		return $this->Set_active($plugin, false);
	}
	//plugin manager
	//public function 
}