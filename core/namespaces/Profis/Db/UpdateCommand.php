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

abstract class UpdateCommand extends Command {
	/** @var array */
	public $params = null;

	/** @var Criteria */
	public $criteria = null;

	/**
	 * @param CommandBuilder $builder
	 * @param string|Schema $schema
	 * @param array $params
	 * @param array|Criteria $criteria
	 */
	public function __construct(CommandBuilder $builder, $schema, $params = array(), $criteria = null) {
		parent::__construct($builder, $schema);
		$this->params = $params;
		$this->criteria = $builder->createCriteria($criteria);
	}

	/**
	 * @param array $params
	 * @param array|Criteria $criteria
	 * @return UpdateCommand
	 * @throws DbException
	 */
	abstract function update($params = null, $criteria = null);
}
