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

use Profis\Db\Criteria;
use \Profis\Db\DbException;
use \PDOStatement;

class SelectCommand extends \Profis\Db\SelectCommand {
	/**
	 * @param array|Criteria $criteria
	 * @return SelectCommand
	 * @throws DbException
	 */
	function execute($criteria = null) {
		$params = array();

		$criteria = ($criteria !== null) ? $this->builder->createCriteria($criteria) : $this->criteria;
		if( !$criteria )
			$criteria = $this->builder->createCriteria();

		// TODO: use criteria to generate SQL
		$select = $criteria->select;
		$from = $this->builder->quoteIdentifier($this->schema->tableName);

		$sql = "SELECT {$select}";
		if( !empty($from) )
			$sql .= " FROM {$from}";

		$conn = $this->builder->connection;
		$statement = $conn->prepare($sql);
		if( !$statement )
			throw new DbException($conn->errorInfo(), $sql, $params);
		if( !$statement->execute($params) )
			throw new DbException($statement->errorInfo(), $sql, $params);
		$this->statement = $statement;
		return $this;
	}

	/**
	 * @return array
	 * @throws DbException
	 */
	function fetch() {
		$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
		return $this->statement->fetch();
	}

	/**
	 * @return array[]
	 * @throws DbException
	 */
	function fetchAll() {
		$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
		return $this->statement->fetchAll();
	}

	/**
	 * @param string $className
	 * @param array $constructorArguments
	 * @return object
	 * @throws DbException
	 */
	function fetchClass($className = '\\stdClass', $constructorArguments = array()) {
		$this->statement->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $className, $constructorArguments);
		return $this->statement->fetch();
	}

	/**
	 * @param string $className
	 * @param array $constructorArguments
	 * @return object[]
	 * @throws DbException
	 */
	function fetchClassAll($className = '\\stdClass', $constructorArguments = array()) {
		$this->statement->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $className, $constructorArguments);
		return $this->statement->fetchAll();
	}

	/**
	 * @param int $columnIndex
	 * @return mixed
	 * @throws DbException
	 */
	function fetchColumn($columnIndex = 0) {
		$this->statement->setFetchMode(\PDO::FETCH_COLUMN, $columnIndex);
		return $this->statement->fetchAll();
	}

	/**
	 * @param int $columnIndex
	 * @return mixed
	 * @throws DbException
	 */
	function fetchScalar($columnIndex = 0) {
		$this->statement->setFetchMode(\PDO::FETCH_COLUMN, $columnIndex);
		return $this->statement->fetch();
	}
}
