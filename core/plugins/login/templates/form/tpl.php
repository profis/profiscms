
<?php

if (!$this->site_users->Is_logged_in()) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.login');
}
else {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.logout');
}


