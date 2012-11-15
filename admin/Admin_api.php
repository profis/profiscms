<?php
/**
 * Class for displaying answers for admin api requests
 */
class Admin_api extends PC_base{
	
	/**
	 * Method generates array of anchor names of page with specified id
	 * @param int $page_id
	 * @return array
	 */
	public function page_anchors($page_id, $ln) {
		return $this->page->Get_page_anchors_by_id($page_id, $ln);
	}
	
}
?>
