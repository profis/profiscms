<?php

class PC_site_user_model extends PC_model {
	
	protected function _set_tables() {
		$this->_table = 'site_users';
	}
	
	protected function _set_rules() {
		$this->_rules = array(
			array(
				'field' => 'email',
				'rule' => 'required',
			),
			array(
				'field' => 'email',
				'rule' => 'unique',
				'message' => 'input_error_email_exists'
			),
			array(
				'field' => 'password',
				'rule' => 'password',
			),
			array(
				'field' => 'password',
				'rule' => 'required',
			),
			array(
				'field' => 'name',
				'rule' => 'name'
			),
			array(
				'field' => 'name',
				'rule' => 'required',
			),
			array(
				'field' => 'login',
				'rule' => 'required',
			),
			array(
				'field' => 'login',
				'rule' => 'unique',
				'message' => 'input_error_user_exists'
			),
		);
	}
	
	protected function _set_filters() {
		$this->_filters = array(
			array(
				'field' => 'name',
				'filter' => 'trim'
			),
			array(
				'field' => 'login',
				'filter' => 'trim'
			)
		);
		if ($this->_id) {
			$this->_filters[] = array(
				'field' => 'password',
				'filter' => 'remove_empty'
			);
		}
	}
	
	protected function _set_sanitize_filters() {
		$this->_sanitize_filters = array(
			array(
				'field' => 'password',
				'filter' => 'sha1'
			),
			array(
				'field' => 'password',
				'filter' => 'md5'
			),
			array(
				'field' => 'banned',
				'filter' => 'boolean',
				'extra' => true
			)
		);
	}
	
}
