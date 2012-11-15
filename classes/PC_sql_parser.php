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

/**
* Class used to help to format the SQL queries.
*/
final class PC_sql_parser extends PC_base {
	/**
	* Field used to store the instance default DB driver name.
	*/
	private $default_driver = 'mysql';
	
	/**
	* Field used to store the instance fields.
	*/
	private $fields = array();
	
	/**
	* Class constructor used to initialize fields, prepare obeject to be usable, etc. Constructor uses runtime variable $cfg. In the
	* constructor field of the instance "driver" is set to "$cfg['db']['type']".
	*/
	public function Init() {
		global $cfg;
		$this->driver = $cfg['db']['type'];
	}
	
	/**
	* Method used to simply access this instance private variable "default_driver".
	* @return string name of the instance default DB driver.
	*/
	public function Get_default_driver() {
		return $this->default_driver;
	}
	
	/**
	* Method used to simply set this instance private variable "default_driver".
	* @param string $driver given name of DB driver to be set as default driver to this instance.
	* @return bool TRUE allways.
	*/
	public function Set_default_driver($driver) {
		$this->driver = $driver;
		return true;
	}

	/**
	* Method used to select appropriate query of given array to match known driver types.
	* @param mixed $queries given array of queries for specific DB drivers.
	* @return mixed FALSE if no queries submited to the method, or appropriate query for the known driver otherwise.
	*/
	public function format($queries) {
		if (!count($queries)) return false;
		//try current driver specific query
		$query =& $queries[$this->driver];
		if (isset($query)) return $query;
		//try default driver query
		$query =& $queries[$this->default_driver];
		if (isset($query)) return $query;
		//try anything else!
		return $queries[key($queries)];
	}
	/**
	* Method used to acquire parts of mysql and pgsql queries which are different. For example "group_concat" construct.
	* @param string $expression given part of query to be added to returned query.
	* @param mixed $options given options for the query.
	* @return mixed array containing mysql and pgsql queries.
	* @see PC_sql_parser::format().
	*/
	public function group_concat($expression, $options=array()) {
		//mysql
		$mysql = 'group_concat('.(v($options['distinct'])===true?'distinct ':'').$expression;
		if (is_array($options) && count($options)) {
			if (isset($options['order'])) {
				$mysql .= ' order by '.($options['order']['by']).(v($options['order']['type'])=='desc'?' desc':'');
			}
			if (isset($options['separator'])) {
				$mysql .= " separator '".$options['separator']."'";
			}
		}
		$mysql .= ')';
		//postgre
		$pgsql = "array_to_string(array_agg(".(v($options['distinct'])===true?'distinct ':'').$expression;
		if (isset($options['order'])) {
			$pgsql .= ' order by '.($options['order']['by']).(v($options['order']['type'])=='desc'?' desc':'');
		}
		$pgsql .= ')';
		if (isset($options['separator'])) {
			$pgsql .= ",'".$options['separator']."'";
		}
		$pgsql .= ')';
		return $this->format(array(
			'mysql' => $mysql,
			'pgsql' => $pgsql
		));
	}
	
	/**
	* Method used to acquire parts of mysql and pgsql queries which are different. For example "concat_ws" construct. To this method culd be supplied
	* any number of variables. They are obtained with internally with function "func_get_args()".
	* @return mixed array containing mysql and pgsql queries.
	* @see PC_sql_parser::format().
	*/
	public function concat_ws() {
		$args = func_get_args();
		if (count($args) < 2) return false;
		$separator = array_shift($args);
		//echo implode("||".array_shift($args)."||", $args);
		return $this->format(array(
			'mysql' => "concat_ws('".$separator."', ".implode(",", $args).")",
			'pgsql' => implode("||'".$separator."'||", $args)
		));
	}
	
	/**
	* Method used to get DB cast expression string.
	* @param string $expression given part of query to be used as part for returned expression.
	* @param string $type given pgsql data type.
	* @return string expression to be used for forming queries to appropriate driver.
	*/
	public function cast($expression, $type) {
		if ($this->driver == 'pgsql') return "cast($expression as $type)";
		return $expression;
	}
	
	/**
	* Method used to get formated table name using runtime variable value "$cfg['db']['prefix']".
	* @param string $table given partly name for new table.
	* @return string full name of new table.
	*/
	public function Get_sequence($table) {
		global $cfg;
		return $cfg['db']['prefix'].$table.'_id_seq';
	}
	
	/**
	* Method used to acquire parts of mysql and pgsql queries which are different. For example "like" construct.
	* @param string $what given expression to be used for checking as the similarity subject.
	* @param bool $case_sensitive given indication if letter casing plays a part.
	* @return mixed array containing mysql and pgsql queries.
	* @see PC_sql_parser::format().
	*/
	public function like($what, $case_sensitive=false) {
		return $this->format(array(
			'mysql' => "like $what".($case_sensitive?' collate '.v($this->cfg['db']['collation'], 'utf8_general_ci'):''),
			'pgsql' => ($case_sensitive?'':'i')."like $what"
		));
	}
	
	/**
	* Method used to add new field to this instance variable "fields".
	* @param string $table given name of the table.
	* @param mixed $fields given array containing given table fields.
	* @return bool TRUE allways.
	*/
	public function Register_fields(string $table, array $fields) {
		$this->fields[$table] = array_merge($this->fields[$table], $fields);
		return true;
	}
	/*public function prepare($type, $table, $fields, $where, $order, $limit) {
		$statement = new PC_database_statement;
		$statement
	}*/
	
	/**
	* Method used to acquire parts of mysql and pgsql queries which are different. For example "group_by" construct.
	* @param string $str given expression used for grouping records in the query.
	* @return mixed array containing mysql and pgsql queries.
	* @see PC_sql_parser::format().
	*/
	public function group_by($str) {
		return $this->format(array(
			'mysql' => substr($str, 0, strpos($str, ',')),
			'pgsql' => $str
		));
	}
	/**
	* @todo implement or remove.
	*/
	public function in($array) {
		$c = count($array);
		if (!$c) return false;
		return 'in('.implode(',', array_fill(0, $c, '?')).')';
	}
	
	/**
	* @todo implement or remove.
	*/
	public function escape($v) {
		return $this->db->quote($v);
	} //alias to PDO->quote();
	
	/**
	* @todo implement or remove.
	*/
	public function _if() {} //if in postgres == case when then end...
	/*public function Implode($glue, $arr, $cast) {
		
	}*/
	public function Replace_variables(&$sql_string) {
		$strings = array(
			'{db}',
			'{prefix}'
		);
		$replacements = array(
			$this->cfg['db']['name'],
			$this->cfg['db']['prefix']
		);
		$sql_string = str_replace($strings, $replacements, $sql_string);
	}
}