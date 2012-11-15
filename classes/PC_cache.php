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
	* A base class for caching mechanisms. It does not really cache anything.
	* Change config $cfg["cache"]["class"] to PC_file_cache or some other
	* class deriving from PC_cache if you need caching to get enabled.
	* 
	* @property string $keyPrefix;
	* @property integer $defaultExpireTime;
	*/
	class PC_cache {
		public function get($key) { return null; }
		public function set($key, $value, $expire = null) { }
		public function delete($key) { }
		public function flush() { }
		
		private $activeCaching = Array();
		public $keyPrefix = "";
		public $defaultExpireTime = 600;
		
		public function __construct() {
			global $cfg;
			$this->keyPrefix = md5(__FILE__) . "/";
			if( isset($cfg["cache"]["defaultExpireTime"]) ) {	
				$expire = $cfg["cache"]["defaultExpireTime"];
				if( is_numeric($expire) )
					$this->defaultExpireTime = $expire;
			}
		}
		
		protected function serialize($key, &$value, $expire) {
			if( $value === null ) {
				$this->delete($key);
				return;
			}
			if( is_numeric($expire) )
				$expire += time();
			else
				$expire = is_string($expire) ? @strtotime($expire) : null;
			if( !$expire )
				$expire = $this->defaultExpireTime + time();
			return serialize(Array($key, $expire, $value));
		}
		
		protected function unserialize($key, &$data) {
			$info = @unserialize($data);
			if( $info === false )
				return null;
			if( !is_array($info) || $info[0] !== $key || $info[1] < time() ) {
				$this->delete($key);
				return null;
			}
			return $info[2];
		}
		
		protected function key($key) {
			return $this->keyPrefix . $key;
		}
		
		public function begin($key, $expire = null) {
			$val = $this->get($key);
			if( $val === null ) {
				if( !ob_start() )
					throw new Exception("Could not start output buffering.");
				array_push($this->activeCaching, Array($key, $expire));
				return true;
			}
			echo $val;
			return false;
		}
		
		public function end() {
			$buf = array_pop($this->activeCaching);
			$this->set($buf[0], ob_get_flush(), $buf[1]);
		}
	}