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

abstract class SelectCommand extends Command {
	/** @var Criteria */
	public $criteria = null;

	/** @var \PDOStatement */
	protected $statement = null;

	/**
	 * @param CommandBuilder $builder
	 * @param string|Schema $schema
	 * @param array|Criteria $criteria
	 */
	public function __construct(CommandBuilder $builder, $schema, $criteria = null) {
		parent::__construct($builder, $schema);
		$this->criteria = $builder->createCriteria($criteria);
	}

	/**
	 * @param array|Criteria $criteria
	 * @return SelectCommand
	 * @throws DbException
	 */
	abstract function execute($criteria = null);

	/**
	 * @return array
	 * @throws DbException
	 */
	abstract function fetch();

	/**
	 * @return array[]
	 * @throws DbException
	 */
	abstract function fetchAll();

	/**
	 * @param string $className
	 * @param array $constructorArguments
	 * @return object
	 * @throws DbException
	 */
	abstract function fetchClass($className, $constructorArguments);

	/**
	 * @param string $className
	 * @param array $constructorArguments
	 * @return object[]
	 * @throws DbException
	 */
	abstract function fetchClassAll($className, $constructorArguments);

	/**
	 * @param int $columnIndex
	 * @return mixed
	 * @throws DbException
	 */
	abstract function fetchColumn($columnIndex);

	/**
	 * @param int $columnIndex
	 * @return mixed
	 * @throws DbException
	 */
	abstract function fetchScalar($columnIndex);

	/**
	 * @param array|Criteria $criteria
	 * @return array
	 * @throws DbException
	 */
	function query($criteria = null) {
		return $this->execute($criteria)->fetch();
	}

	/**
	 * @param array|Criteria $criteria
	 * @return array
	 * @throws DbException
	 */
	function queryAll($criteria = null) {
		return $this->execute($criteria)->fetchAll();
	}

	/**
	 * @param string $className
	 * @param array $constructorArguments
	 * @param array|Criteria $criteria
	 * @return object
	 * @throws DbException
	 */
	function queryClass($className = '\\stdClass', $constructorArguments = array(), $criteria = null) {
		return $this->execute($criteria)->fetchClass($className, $constructorArguments);
	}

	/**
	 * @param string $className
	 * @param array $constructorArguments
	 * @param array|Criteria $criteria
	 * @return object[]
	 * @throws DbException
	 */
	function queryClassAll($className = '\\stdClass', $constructorArguments = array(), $criteria = null) {
		return $this->execute($criteria)->fetchClassAll($className, $constructorArguments);
	}

	/**
	 * @param int $columnIndex
	 * @param array|Criteria $criteria
	 * @return array
	 * @throws DbException
	 */
	function queryColumn($columnIndex = 0, $criteria = null) {
		return $this->execute($criteria)->fetchColumn($columnIndex);
	}

	/**
	 * @param int $columnIndex
	 * @param array|Criteria $criteria
	 * @return mixed
	 * @throws DbException
	 */
	function queryScalar($columnIndex = 0, $criteria = null) {
		return $this->execute($criteria)->fetchScalar($columnIndex);
	}
}
