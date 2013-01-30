<?php

class PC_page_model extends PC_model {
	
	protected function _set_tables() {
		$this->_table = 'pages';
		$this->_content_table = 'content';
		$this->_content_table_relation_col = 'pid';
	}
	
}
