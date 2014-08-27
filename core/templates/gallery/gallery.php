<?php
/**
 * @var PC_gallery_widget $this
 * @var array $data
 * @var string $tpl_group
 */
if( $data['style'] )
	$this->site->Add_stylesheet($this->Get_resource_path('css/gallery.css'), 2);

$this->site->Add_script($this->Get_resource_path('js/gallery.js'), 2);

include $this->core->Get_tpl_path($tpl_group, $this->_template . '.' . $data['view']);
