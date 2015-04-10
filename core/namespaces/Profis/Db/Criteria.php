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

class Criteria {
	/** @var string */
	public $select = '*';

	/**
	 * @param array $criteriaArray An array to build criteria from.
	 */
	public function __construct($criteriaArray = null) {
		if( is_array($criteriaArray) )
			$this->fromArray($criteriaArray);
	}

	/**
	 * @param $criteriaArray
	 * @return $this
	 */
	public function fromArray($criteriaArray) {
		if( isset($criteriaArray['select']) )
			$this->select = $criteriaArray['select'];
		return $this;
	}
} 