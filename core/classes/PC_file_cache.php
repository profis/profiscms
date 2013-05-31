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
	* This class implements caching mechanism using file system.
	*/
	class PC_file_cache extends PC_cache {
		private $cacheFolder = "cache";
		
		public function __construct() {
			global $cfg;
			if( isset($cfg["cache"]["folder"]) )
				$this->cacheFolder = $cfg["cache"]["folder"];
			$this->cacheFolder = trim($this->cacheFolder, "/");
			if( $this->cacheFolder === "" )
				throw new Exception("Invalid caching folder setting in configuration");
			$this->cacheFolder = $cfg["path"]["base"] . "/" . $this->cacheFolder . "/";
		}
		
		public function get($key) {
			$file = $this->cacheFolder . md5($this->key($key));
			if( is_file($file) ) {
				$data = @file_get_contents($file);
				return $this->unserialize($key, $data);
			}
			return null;
		}
		
		public function set($key, $value, $expire = null) {
			$file = $this->cacheFolder . md5($this->key($key));
			@file_put_contents($file, $this->serialize($key, $value, $expire));
		}
		
		public function delete($key) {
			@unlink($this->cacheFolder . md5($this->key($key)));
		}
		
		public function flush() {
			$files = glob($this->cacheFolder . "*");
			if (!is_array($files)) {
				return;
			}
			foreach( $files as $file ) {
				$fname = basename($file);
				if( $fname[0] != "." && strlen($fname) == 32 )
					@unlink($file);
			}					
		}
	}