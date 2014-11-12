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

/**
 * Class DbException
 *
 * An exception that should be thrown on database errors
 *
 * @package Profis\Db
 */
class DbException extends \Exception {
	protected $query = null;
	protected $params = null;
	protected $errorInfo = null;

	public function __construct($errorInfo = null, $query = null, $params = null, $message = 'Failed to execute query', $previousException = null) {
		global $cfg;

		$this->query = $query;
		$this->params = $params;
		$this->errorInfo = $errorInfo;

		if( v($cfg['debug_output']) ) {
			if( is_array($this->errorInfo) ) {
				switch( $errorInfo[0] ) {
					case 'HY093': $errorInfo[2] = 'Bound parameter list does not match parameters used in query'; break;
				}
				$message .= ': [' . $errorInfo[0] . '] ' . $errorInfo[2];
			}
			if( $query ) {
				$message .= " \nQuery: " . $query;
				if( $params )
					$message .= " \nBindings: " . print_r($params, true);
			}
		}

		parent::__construct($message, is_array($errorInfo) ? intval($errorInfo[1]) : 0, $previousException);
	}

	public function getQuery() {
		return $this->query;
	}

	public function getParams() {
		return $this->params;
	}

	public function getErrorInfo() {
		return $this->errorInfo;
	}
}