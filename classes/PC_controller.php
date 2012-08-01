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

abstract class PC_controller extends PC_base {
	public function Init() {
		$this->text =& $this->site->text;
		$this->name = substr(get_class($this), strlen('PC_controller_'));
	}
	abstract public function Process($data);
	final public function Get_path() {
		return $this->core->path['plugins'].$this->name.'/';
	}
	final public function &Render($tpl=null, $return_only=false) {
		$tpl_prefix = 'PC_template';
		$tpl_file = '';
		if (!empty($tpl)) $tpl_file .= '_'.(string)$tpl;
		$tpl_file .= '.php';
		$tpl_path = $this->core->Get_theme_path(null, false).$tpl_prefix.'_'.$this->name.$tpl_file;
		if (!is_file($tpl_path)) {
			$tpl_path = $this->Get_path().$tpl_prefix.$tpl_file;
		}
		$this->Output_start();
		require($tpl_path);
		if ($return_only) {
			$this->Output_end($text);
			return $text;
		}
		else {
			$this->Output_end($this->text);
			return $this->text;
		}
	}
	/* Also include specified template while calling $this->Render() */
	final public function Include_template($tpl, $where) {
		//$where - after, before
	}
}