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

namespace Profis\Db;

use \Profis\Db\Components\DbConnection;

abstract class CommandBuilder {
	/** @var DbConnection */
	public $connection;

	/**
	 * @param DbConnection $connection
	 */
	public function __construct(DbConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param string|Schema  $schema
	 * @param array $params
	 * @return InsertCommand
	 */
	abstract function createInsertCommand($schema, $params = array());

	/**
	 * @param string|Schema  $schema
	 * @param array|Criteria $criteria
	 * @return SelectCommand
	 */
	abstract function createSelectCommand($schema, $criteria = null);

	/**
	 * @param string|Schema  $schema
	 * @param array $params
	 * @param array|Criteria $criteria
	 * @return UpdateCommand
	 */
	abstract function createUpdateCommand($schema, $params = array(), $criteria = null);

	/**
	 * Makes identifier quoted according to driver specifications.
	 *
	 * @param string $name
	 * @return string
	 */
	abstract function quoteIdentifier($name);

	/**
	 * @param array|Criteria $criteria
	 * @return Criteria
	 */
	public function createCriteria($criteria) {
		if( $criteria instanceof Criteria )
			return clone $criteria;
		return new $this->connection->criteriaClass($criteria);
	}

	/**
	 * @param string|Schema $schema
	 * @return Schema
	 */
	public function getSchema($schema) {
		if( $schema instanceof Schema )
			return $schema;
		return new $this->connection->schemaClass($schema, $this->connection->prefix);
	}
}