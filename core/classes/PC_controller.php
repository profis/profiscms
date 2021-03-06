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
	/** @var string */
	public $name = '';

	/** @var string */
	public $text = '';

	public function Init($do_not_bind_to_site = false) {
		if ($do_not_bind_to_site) {
			$this->text = '';
		}
		else {
			$this->text =& $this->site->text;
		}
		
		$this_class = get_class($this);
		$offset = intval(strpos($this_class, 'pc_controller_'));
		$this->name = substr($this_class, $offset + strlen('PC_controller_'));
	}

	abstract public function Process($data);
	final public function Get_path() {
		return $this->core->Get_path('plugins', '', $this->name);
	}

	final public function &Render($tpl=null, $return_only=false, $vars = array()) {
		$tpl_prefix = 'PC_template';
		$tpl_file = '';
		if (!empty($tpl)) $tpl_file .= '_'.(string)$tpl;
		$ln_tpl_file = $tpl_file . '.' . $this->site->ln . '.php';
		$tpl_file .= '.php';
		$tpl_path = $this->core->Get_theme_path(null, false).$tpl_prefix.'_'.$this->name . $ln_tpl_file;
		if (!is_file($tpl_path)) {
			$tpl_path = $this->core->Get_theme_path(null, false).$tpl_prefix.'_'.$this->name . $tpl_file;
			if (!is_file($tpl_path)) {
				$tpl_path = $this->Get_path().$tpl_prefix.$ln_tpl_file;
				if (!is_file($tpl_path)) {
					$tpl_path = $this->Get_path().$tpl_prefix.$tpl_file;
				}
			}
		}

		if ($return_only !== null)
			$this->Output_start();

		if (is_array($vars))
			extract($vars);

		require($tpl_path);

		if ($return_only === null) {
			$null = null;
			return $null;
		}

		if ($return_only) {
			$text = '';
			$this->Output_end($text);
			return $text;
		}

		$this->Output_end($this->text);
		return $this->text;
	}

	/* Also include specified template while calling $this->Render() */
	final public function Include_template($tpl, $vars = array()) {
		$this->Render($tpl, null, $vars);
	}
	
	final public function Get_variable($key, $ln = null, $default = '') {
		$variable = $this->core->Get_variable($key, $ln, $this->name);
		if (empty($variable)) {
			$variable = $this->core->Get_variable($this->name . '_' . $key, $ln);
		}
		if (empty($variable)) {
			$variable = $default;
		}
		return $variable;
	}
}