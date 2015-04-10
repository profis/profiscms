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
use \PDO;

class NestedPDO extends PDO {
	private $transactionNestingLevel = 0;

	public function beginTransaction() {
		$q = null;
		if( $this->transactionNestingLevel == 0 )
			$result = parent::beginTransaction();
		else
			$result = $this->exec($q = "SAVEPOINT LEVEL{$this->transactionNestingLevel}");
		if( !$result )
			throw new DbTransactionException($this->errorInfo(), $q, null, "Could not begin a transaction");
		$this->transactionNestingLevel++;
	}

	public function commit() {
		if( $this->transactionNestingLevel == 0 )
			throw new DbTransactionException(null, null, null, "Inconsistent number of beginTransaction() and commit() / rollBack() calls. Please check your code so that only one commit() or rollBack() method is called per transaction that was started with beginTransaction().");
		$this->transactionNestingLevel--;
		$q = null;
		if( $this->transactionNestingLevel == 0 )
			$result = parent::commit();
		else
			$result = $this->exec($q = "RELEASE SAVEPOINT LEVEL{$this->transactionNestingLevel}");
		if( !$result )
			throw new DbTransactionException($this->errorInfo(), $q, null, "Could not commit a transaction");
	}

	public function rollBack() {
		if( $this->transactionNestingLevel == 0 )
			throw new DbTransactionException(null, null, null, "Inconsistent number of beginTransaction() and commit() / rollBack() calls. Please check your code so that only one commit() or rollBack() method is called per transaction that was started with beginTransaction().");
		$this->transactionNestingLevel--;
		$q = null;
		$result = true;
		if ($this->transactionNestingLevel == 0)
			$result = parent::rollBack();
		else
			$result = $this->exec($q = "ROLLBACK TO SAVEPOINT LEVEL{$this->transactionNestingLevel}");
		if( !$result )
			throw new DbTransactionException($this->errorInfo(), $q, null, "Could not roll back a transaction");
	}
} 