<?php

class PC_hmenu_widget extends PC_menu_widget {
	
	public function get_template_group() {
		 return parent::get_template_group() . ':hmenu';
	}
	
}