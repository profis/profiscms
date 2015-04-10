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

namespace Profis;

/**
 * Class App
 *
 * @property \Profis\Db\Components\DbConnection $db
 *
 * @package Profis
 */
abstract class App {
	/** @var \Profis\App */
	static $instance = null;

	/** @var array */
	public $config = array();

	/** @var \Profis\Component[] */
	protected $components = array();

	/**
	 * @return App
	 */
	public static function app() {
		return self::$instance;
	}

	public function __isset($propertyName) {
		return isset($this->components[$propertyName]);
	}

	public function __get($propertyName) {
		if( isset($this->components[$propertyName]) ) {
			$component = $this->components[$propertyName];
			if( !$component->initialized ) {
				$component->init();
				$component->initialized = true;
			}
			return $this->components[$propertyName];
		}
		return null;
	}

	public function __construct($config = array()) {
		self::$instance = $this;
		$this->config = array_replace_recursive($this->getDefaultConfig(), $config);
	}

	public function run() {
		$this->initComponents();
	}

	public function getDefaultConfig() {
		return array(
			'components' => array(
				'db' => array(
					'class' => '\\Profis\\Db\\Components\\DbConnection',
				),
			),
		);
	}

	public function initComponents() {
		if( !isset($this->config['components']) || !is_array($this->config['components']) )
			$this->config['components'] = array();

		foreach( $this->config['components'] as $componentId => $componentInfo ) {
			if( !isset($componentInfo['class']) )
				throw new \Exception('Cannot add a component "' . $componentId . '" to the application: class is not specified in configuration');
			$class = $componentInfo['class'];
			$component = new $class();
			foreach( $componentInfo as $key => $value ) {
				if( $key !== 'class' && $key != 'eagerInit' )
					$component->$key = $value;
			}
			$this->components[$componentId] = $component;
		}

		foreach( $this->config['components'] as $componentId => $componentInfo ) {
			if( isset($componentInfo['eagerInit']) && $componentInfo['eagerInit'] ) {
				$this->components[$componentId]->init();
				$this->components[$componentId]->initialized = true;
			}
		}
	}

	public function end($exitCode = 0) {
		exit($exitCode);
	}
}