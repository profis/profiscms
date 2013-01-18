<?php
//ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
//
//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
* Class used to speed up website by using storing data in memory technique.
*/
final class PC_memstore extends PC_base {
	/**
	* Class field of array type. This field used to store all caching data.
	*/
	private $_cache = array();
	
	/**
	* Method for storing data in _cache object for later use.
	* @param mixed $keys used to store caching array keys.
	* @param mixed $data stands for the data to be stored.
	* @return mixed bool FALSE if no caching done and reference to caching object. 
	*/
	public function &Cache($keys, $data, $overwrite=true) {
		//do not overwrite if cached key exists
		if (!$overwrite && $this->Is_cached($keys)) return false;
		//cache
		if (!is_array($keys)) $keys = array($keys);
		$cache =& $this->_cache;
		$total_keys = count($keys);
		for ($a=0; $a+1<$total_keys; $a++) {
			if (!isset($cache[$keys[$a]])) {
				$cache[$keys[$a]] = array();
			}
			$cache =& $cache[$keys[$a]];
		}
		$cache[$keys[$total_keys-1]] = $data;
		return $cache[$keys[$total_keys-1]];
	}
	/**
	* Method used to unset the cached objects already stored in cache.
	* @params mixed $keys - any number of keys.
	* @return bool TRUE.
	*/
	public function Uncache() {
		$keys = func_get_args();
		if (is_array($keys[0])) $keys = $keys[0];
		$cache =& $this->_cache;
		foreach ($keys as $key) {
			if (!isset($cache[$key])) return true;
			$cache =& $cache[$key];
		}
		unset($cache);
		return true;
	}
	/**
	* Method used to check if cache object contains object by given keys and if no keys given, if anything is in cache.
	* @params mixed $keys any number of keys to be checked.
	* @return bool TRUE if keys given found in the cache OR if keys does not given, but cache is not empty; and FALSE if cache is empty or cache does not 	* * contain objects by given keys.
	*/
	public function Is_cached() {
		//get keys path to the cache being checked
		$keys = func_get_args();
		if (is_array($keys[0])) $keys = $keys[0];
		//if no arguments specified, return whether is something cached or not
		if (!count($keys)) {
			if (count($this->_cache)) return true;
			else return false;
		}
		//check if cache path exists
		$cache = $this->_cache;
		foreach ($keys as $key) {
			if (!isset($cache[$key])) return false;
			$cache = $cache[$key];
		}
		return true;
	}
	/**
	* Method used to get object from cache by keys.
	* @params mixed $keys any number of keys.
	* return mixed FALSE if no object found in cache by the key.
	*/
	public function &Get() {
		$keys = func_get_args();
		if (is_array($keys[0])) $keys = $keys[0];
		$cache =& $this->_cache;
		foreach ($keys as $key) {
			$false = false;
			if (!isset($cache[$key])) return $false;
			$cache =& $cache[$key];
		}
		return $cache;
	}
}