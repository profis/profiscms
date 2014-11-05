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

namespace Profis\Collections;

use \Profis\Collections\IComparer;

/**
 * Class DataSorter
 *
 * @package Profis\Collections
 */
class DataSorter implements IComparer {
	const DIR_ASCENDING = 0;
	const DIR_DESCENDING = 1;

	const CMP_STRING = 0;
	const CMP_STRING_CI = 1;
	const CMP_NUMERIC = 2;
	const CMP_VERSION = 3;

	protected $keys = array();

	/**
	 * Adds a key that must be used in array / object comparison.
	 *
	 * @param string $key Name of the array key or object field to use when comparing
	 * @param int $direction Direction of comparison for specified key. Must be either DataSorter::DIR_ASCENDING or DataSorter::DIR_DESCENDING. Defaults to DataSorter::DIR_ASCENDING.
	 * @param int|\Profis\Collections\IComparer $type Type of comparison for specified key. Must be either one of DataSorter::CMP_* constants or an instance of class that implements \Profis\Collections\IComparer interface. Defaults to DataSorter::CMP_STRING.
	 */
	public function addKey($key, $direction = self::DIR_ASCENDING, $type = self::CMP_STRING) {
		$this->keys[$key] = array($direction, $type);
	}

	/**
	 * Sorts the array of arrays / objects by keys previously added with addKey() method.
	 *
	 * @param array[]|object[] $data
	 * @see addKey()
	 */
	public function sort(&$data) {
		uasort($data, array($this, 'compare'));
	}

	/**
	 * Compares two arrays / objects by their keys / fields previously added with addKey() method.
	 *
	 * @param array|object $a First array or object to use in comparison.
	 * @param array|object $b Second array or object to use in comparison.
	 * @return int Comparison result. Less than 0 when a < b, 0 when a = b and greater than 0 when a > b.
	 */
	public function compare($a, $b) {
		foreach( $this->keys as $key => $keyData ) {
			$va = is_object($a) ? $a->$key : $a[$key];
			$vb = is_object($b) ? $b->$key : $b[$key];

			$type = $keyData[1];

			if( is_object($type) && $type instanceof IComparer ) {
				/** @var \Profis\Collections\IComparer $type */
				$result = $type->compare($va, $vb);
			}
			else {
				switch( $type ) {
					case self::CMP_STRING: $result = strcmp($va, $vb); break; // this will have problems with UTF-8
					case self::CMP_STRING_CI: $result = strcasecmp($va, $vb); break; // this will have problems with UTF-8
					case self::CMP_NUMERIC: $result = $va - $vb; break;
					case self::CMP_VERSION: $result = version_compare($va, $vb); break;
					default: $result = 0;
				}
			}
			if( $result != 0 )
				return ($keyData[0] == self::DIR_ASCENDING) ? $result : -$result;
		}
		return 0;
	}
}