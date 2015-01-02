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
 * Class ComplexStringComparer
 *
 * This class is used to compare two complex strings that consist of character and numeric text.
 *
 * @see ComplexStringComparer::compare()
 * @package Profis\Collections
 */
class ComplexStringComparer implements IComparer {

	/** @var bool Controls whether characters are compared case sensitive (TRUE) or case insensitive (FALSE). Defaults to FALSE. */
	public $caseSensitive;

	/**
	 * Constructor
	 *
	 * @param bool $caseSensitive Determines whether comparison should be case sensitive (TRUE) or case insensitive (FALSE). Defaults to FALSE.
	 */
	public function __construct($caseSensitive = false) {
		$this->caseSensitive = $caseSensitive;
	}

	/**
	 * Performs a complex comparison of two strings that consist of character and numeric text. The comparison splits
	 * characters apart from numbers and compares them separately: characters are compared as strings and numbers are
	 * compared as numbers. This allows sorting serial numbers and similar values.
	 *
	 * Example:
	 * ```
	 * $comparer = new ComplexStringComparer(false); // FALSE for case insensitive comparison
	 * echo $comparer->compare('TI-18-A', 'ti-18-a'); // outputs 0 (case insensitive)
	 * echo $comparer->compare('TI-018-A', 'TI-18-A'); // outputs 0 (018 is numeric and it equals to 18)
	 * echo $comparer->compare('TI-18-B', 'ti-18-a'); // outputs 1 ("-B" > "-A")
	 * echo $comparer->compare('TI-2-A', 'ti-18-B'); // outputs -1 (2 < 18)
	 * ```
	 *
	 * @param string $a First string to compare.
	 * @param string $b Second string to compare.
	 * @return int Comparison result. Less than 0 when a < b, 0 when a = b and greater than 0 when a > b.
	 */
	public function compare($a, $b) {
		if( $a == $b )
			return 0;
		$d1 = self::decomposeString($a);
		$d2 = self::decomposeString($b);
		$c1 = count($d1);
		$c2 = count($d2);
		$cnt = ($c1<$c2)?$c1:$c2;
		for( $i=0; $i<$cnt; $i++ ) {
			if( $d1[$i][0] === $d2[$i][0] && $d1[$i][1] == $d2[$i][1] )
				continue;
			if( $d1[$i][1] != $d2[$i][1] || !$d1[$i][1] )
				$result = $this->compareStrings($d1[$i][0],$d2[$i][0]);
			else
				$result = intval($d1[$i][0]) - intval($d2[$i][0]);
			if( $result != 0 )
				return $result;
		}
		if( $c1 == $c2 )
			return 0;
		return ($c1<$c2)?-1:1;
	}

	/**
	 * Performs either case sensitive or case insensitive comparison of two simple strings according to the value of
	 * $caseSensitive field.
	 *
	 * @param string $a
	 * @param string $b
	 * @return int Comparison result. Less than 0 when a < b, 0 when a = b and greater than 0 when a > b.
	 */
	public function compareStrings($a, $b) {
		return $this->caseSensitive ? strcmp($a, $b) : strcasecmp($a, $b);
	}

	/**
	 * Decomposes string into character and numeric parts
	 *
	 * @param string $str A string to decompose.
	 * @return array An array of decomposed string components.
	 */
	static function decomposeString($str) {
		$data = array();
		if( preg_match_all('#([0-9]+|[^0-9]+)#su', $str, $mtc) ) {
			foreach($mtc[0] as $part) {
				$data[] = array(
					$part,
					is_numeric($part),
				);
			}
		}
		return $data;
	}
}
