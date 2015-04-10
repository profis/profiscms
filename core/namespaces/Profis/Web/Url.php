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

namespace Profis\Web;

class Url {
	public $scheme = "http";
	public $user = null;
	public $pass = null;
	public $host = null;
	public $port = null; // use default for the scheme when null
	public $path = null;
	public $query = array();
	public $fragment = null;
	public $pathIsLocal = false;

	public static $basePath = '/';

	public function __construct($url = null, $relativeToBase = null) {
		if( $url instanceof Url ) {
			$this->scheme = $url->scheme;
			$this->user = $url->user;
			$this->pass = $url->pass;
			$this->host = $url->host;
			$this->port = $url->port;
			$this->path = $url->path;
			$this->query = $url->query;
			$this->fragment = $url->fragment;
			$this->pathIsLocal = $url->pathIsLocal;
		}
		else if( is_string($url) ) {
			if( empty($url) ) {
				$this->pathIsLocal = true;
			}
			else {
				$parsedData = parse_url($url);
				if( !is_array($parsedData) )
					throw new BadUrlException($url, "Cannot parse given URL");
				foreach( $parsedData as $k => $v ) {
					if( $k == 'query' ) {
						$this->query = array();
						parse_str($v, $this->query);
					}
					else
						$this->$k = $v;
				}
				if( $relativeToBase === null && !isset($parsedData['host']) ) {
					// Whether given url is relative to the base path or not is detected by leading slash
					$relativeToBase = (empty($parsedData['path']) || $parsedData['path'][0] != '/');
				}

				if( !$relativeToBase && ($this->host === null || $this->host === $_SERVER['HTTP_HOST']) ) {
					$len = strlen(self::$basePath);
					if( strlen($this->path) >= $len && substr($this->path, 0, $len) === self::$basePath ) {
						$this->path = substr($this->path, $len);
						$this->pathIsLocal = true;
					}
				}
				else if( $relativeToBase )
					$this->pathIsLocal = true;
			}
		}
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function getDefaultPort() {
		switch($this->scheme) {
			case "ftp": return 21;
			case "ssh": return 22;
			case "http": return 80;
			case "https": return 443;
		}
		return null;
	}

	public function getQueryString() {
		if( is_string($this->query) )
			return $this->query;
		return http_build_query($this->query);
	}

	public function getAbsoluteUrl() {
		$url = $this->scheme . '://';
		if( $this->user !== null ) {
			$url .= $this->user;
			if( $this->pass !== null )
				$url .= ':' . $this->pass;
			$url .= '@';
		}
		$url .= ($this->host !== null) ? $this->host : $_SERVER['HTTP_HOST'];
		if( $this->port !== null && $this->port != $this->getDefaultPort() )
			$url .= ':' . $this->port;
		return $url . $this->getRelativeUrl(true);
	}

	public function getRelativeUrl($fullPath = false) {
		$url = (string)($fullPath ? $this->getFullPath() : $this->path);
		if( !empty($this->query) )
			$url .= '?' . $this->getQueryString();
		if( !empty($this->fragment) )
			$url .= '#' . $this->fragment;
		return $url;
	}

	public function getFullPath() {
		return (string)($this->pathIsLocal ? (self::$basePath . $this->path) : $this->path);
	}

	public function __toString() {
		if( $this->host === null || $this->host == $_SERVER['HTTP_HOST'] )
			return $this->getRelativeUrl();
		return $this->getAbsoluteUrl();
	}

	static $_currentUrl = null;
	static function getCurrentUrl() {
		if( self::$_currentUrl === null ) {
			$url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
			if( isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== FALSE )
				$url .= $_SERVER['REQUEST_URI'];
			else {
				$pth = preg_replace('/index\\.php$/i', '', $_SERVER['PHP_SELF']);
				$url = $pth . ((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? ('?' . $_SERVER['QUERY_STRING']) : '');
			}
			self::$_currentUrl = new Url($url);
		}
		return clone self::$_currentUrl;
	}
}