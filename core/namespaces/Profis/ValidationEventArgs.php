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

namespace Profis;

class ValidationEventArgs extends DefaultPreventableEventArgs {
	/** @var mixed */
	protected $object;
	
	/** @var array */
	protected $errors = array();

	/**
	 * @param mixed $validationObject
	 */
	public function __construct($validationObject) {
		$this->object = $validationObject;
	}

	/**
	 * Returns an object that must be validated by an event handler.
	 *
	 * @return mixed An object that must be validated.
	 */
	public function getObject() {
		return $this->object;
	}

	/**
	 * Adds a validation error to the error list.
	 * 
	 * @param string $code Error code. Usually a field name.
	 * @param string $text Error message.
	 */
	public function addError($code, $text) {
		$this->errors[$code] = $text;
	}

	/**
	 * Returns whether there were any errors or not.
	 *
	 * @return bool TRUE if there were any errors during vlidation.
	 */
	public function hasErrors() {
		return !empty($this->errors);
	}

	/**
	 * Returns all errors that were detected during validation.
	 * 
	 * @return array List of all registered validation errors.
	 */
	public function getErrors() {
		return $this->errors;
	}
} 