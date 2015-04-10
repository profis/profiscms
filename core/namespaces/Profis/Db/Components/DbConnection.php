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

namespace Profis\Db\Components;
use \PDO;
use \PDOStatement;
use \Profis\Component;
use \Profis\Db\Criteria;
use \Profis\Db\Schema;

/**
 * Class DbConnection
 * @package Profis\Db\Components
 *
 * @method string quote(string $string)
 * @method PDOStatement prepare(string $statement, array $driver_options = array())
 * @method array errorInfo()
 */
class DbConnection extends Component {
	/** @var \Profis\Db\NestedPDO */
	protected $driver = null;

	/** @var string The class that must be used for the database connection. The driver must be either \PDO itself or extend that class. */
	public $pdoClass = '\\Profis\\Db\\NestedPDO';

	/** @var string The class that will be used when converting criteria from array to the object. */
	public $criteriaClass = '\\Profis\\Db\\Criteria';

	/** @var string The class of the table schema. */
	public $schemaClass = '\\Profis\\Db\\Schema';

	public $dsn = null;
	
	public $user = null;
	
	public $pass = null;
	
	public $prefix = null;
	
	public $pdoOptions = array();

	/** @var \Profis\Db\CommandBuilder */
	public $commandBuilder = null;

	public $commandBuilderClass = null;

	public $commandBuilderClassMap = array(
		'mysql' => '\\Profis\\Db\\MySQL\\CommandBuilder',
		'pgsql' => '\\Profis\\Db\\Postgres\\CommandBuilder',
		'sqlite' => '\\Profis\\Db\\SQLite\\CommandBuilder',
	);

	public function __construct() {
	}

	public function init() {
		$this->pdoOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
		$this->driver = new $this->pdoClass($this->dsn, $this->user, $this->pass, $this->pdoOptions);
		$this->driver->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if( $this->commandBuilderClass === null
			&& !empty($this->commandBuilderClassMap)
			&& is_array($this->commandBuilderClassMap)
			&& preg_match('#^(.+?):.#', $this->dsn, $match)
			&& isset($this->commandBuilderClassMap[$match[1]]) )
				$this->commandBuilderClass = $this->commandBuilderClassMap[$match[1]];
		if( $this->commandBuilderClass !== null )
			$this->commandBuilder = new $this->commandBuilderClass($this);
	}

	/**
	 * @param array|Criteria $criteria
	 * @return Criteria
	 */
	public function createCriteria($criteria) {
		return $this->commandBuilder->createCriteria($criteria);
	}

	/**
	 * @param string|Schema $schema
	 * @return Schema
	 */
	public function getSchema($schema) {
		return $this->commandBuilder->getSchema($schema);
	}

	public function __call($name, $args) {
		return call_user_func_array(array($this->driver, $name), $args);
	}
}