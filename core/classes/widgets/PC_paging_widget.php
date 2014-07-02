<?php

class PC_paging_widget extends PC_widget {
	
	protected $_template_group = 'paging';
	
	protected function _get_default_config() {
		return array(
			'per_page' => 20,
			'max_paging_items' => 5,
			'total_items' => 1,
			'base_url' => '',
			'get_vars' => '_all',
			'label_for_first_page' => '&laquo;',
			'label_for_prev_page' => '&lt;',
			'label_for_next_page' => '&gt;',
			'label_for_last_page' => '&raquo;',
		);
	}

	protected function _get_items($paging) {
		//print_pre($paging);
		
		$items = array();
		
		$first = array(
			'label' => $this->_config['label_for_first_page'],
			'link' => PC_utils::getUrl($this->_config['base_url'], $this->_config['get_vars'])
		);
		
		$prev = array(
			'label' => $this->_config['label_for_prev_page'],
			'link' => $this->_config['base_url']
		);
		
		if ($paging['pi'] <= 1) {
			$first['disabled'] = true;
			$prev['disabled'] = true;
		}
		elseif ($paging['pi'] > 2) {
			$prev['link'] = pc_append_route($this->_config['base_url'], 'page' . ($paging['pi'] - 1));
		}
		
		$prev['link'] = PC_utils::getUrl($prev['link'], $this->_config['get_vars']);
		
		if( !v($this->_config['disable_first']) )
			$items[] = $first;
		if( !v($this->_config['disable_prev']) )
			$items[] = $prev;
		
		for ($i = 1; $i <= $paging['pages']; $i++) {
			$link = $this->_config['base_url'];
			if ($i > 1) {
				$link = pc_append_route($link, 'page' . $i);
			}
			$link = PC_utils::getUrl($link, $this->_config['get_vars']);
			if( $i < $paging['ps'] or $i > $paging['pe']) {
				continue;
			}
			if ($paging['pi'] == $i) {
				$items[] = array(
					'label' => $i,
					//'disabled' => true,
					'active' => true
				);
			}
			else {
				$items[] = array(
					'label' => $i,
					'link' => $link
				);
			}
				//echo '<a href="' . htmlspecialchars(PC_utils::getCurrUrl(array('page' => $i))) . '" title="' . $i . '">' . $i . '</a>';
		}
		
		$next_page = min($paging['pages'], $paging['pi'] + 1);
		$next = array(
			'label' => $this->_config['label_for_next_page'],
		);
		if ($next_page > $paging['pi']) {
			$next['link'] = pc_append_route($this->_config['base_url'], 'page' . $next_page);
			$next['link'] = PC_utils::getUrl($next['link'], $this->_config['get_vars']);
		}
		else {
			$next['disabled'] = true;
		} 
			
		$last = array(
			'label' => $this->_config['label_for_last_page'],
		);
		
		if ($paging['pi'] < $paging['pages']) {
			$last['link'] = pc_append_route($this->_config['base_url'], 'page' . $paging['pages']);
			$last['link'] = PC_utils::getUrl($last['link'], $this->_config['get_vars']);
		}
		else {
			$last['disabled'] = true;
		}
		
		
		if( !v($this->_config['disable_next']) )
			$items[] = $next;
		if( !v($this->_config['disable_last']) )
			$items[] = $last;
		
		return $items;
	}
	
	public function get_data() {
		//print_pre($this->_config);
		$paging = PC_utils::pagingInit($this->_config['per_page'], $this->_config['max_paging_items']);
		
		PC_Utils::pagingGet($paging, $this->_config['total_items']);
		
		return array(
			'items' => $this->_get_items($paging),
			'max_page' => $paging['pages']
		);
	}
	
}