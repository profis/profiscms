<?php
/**
 * @var PC_plugin_login_form_widget $this
 * @var string $tpl_group
 * @var string $errors_html
 * @var string $pass_change_link
 * @var string $remind_username_link
 * @var string $register_link
 */
if (!$this->site_users->Is_logged_in()) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.login');
}
else {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.logout');
}


