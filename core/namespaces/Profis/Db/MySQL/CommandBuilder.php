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

namespace Profis\Db\MySQL;

use \Profis\Db\Schema;
use \Profis\Db\Criteria;

class CommandBuilder extends \Profis\Db\Commandbuilder {
	/**
	 * @param string|Schema  $schema
	 * @param array $params
	 * @return InsertCommand
	 */
	function createInsertCommand($schema, $params = array()) {
		return new InsertCommand($this, $schema, $params);
	}

	/**
	 * @param string|Schema  $schema
	 * @param array|Criteria $criteria
	 * @return SelectCommand
	 */
	function createSelectCommand($schema, $criteria = null) {
		return new SelectCommand($this, $schema, $criteria);
	}

	/**
	 * @param string|Schema  $schema
	 * @param array $params
	 * @param array|Criteria $criteria
	 * @return UpdateCommand
	 */
	function createUpdateCommand($schema, $params = array(), $criteria = null) {
		return new UpdateCommand($this, $schema, $params, $criteria);
	}

	/**
	 * Makes identifier quoted according to driver specifications.
	 *
	 * @param string $name
	 * @return string
	 */
	public function quoteIdentifier($name) {
		return '`' . $name . '`';
	}

	/**
	 * @param string $schemaName
	 * @return Schema
	 */
	public function getSchema($schemaName) {
		$schema = parent::getSchema($schemaName);
		return $schema;
	}
}