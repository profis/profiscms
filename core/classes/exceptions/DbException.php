<?php
/**
 * An exception that should be thrown on database errors
 */
class DbException extends Exception {
	protected $query = null;
	protected $params = null;
	protected $errorInfo = null;

	public function DbException($errorInfo = null, $query = null, $params = null, $message = 'Failed to execute query', $previousException = null) {
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