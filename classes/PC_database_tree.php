<?php
final class PC_database_tree extends PC_base {
	public function Get_cols(&$params) {
		return array(
			'id'=> (isset($params->cols['id'])?$params->cols['id']:'id'),
			'left'=> (isset($params->cols['left'])?$params->cols['left']:'lft'),
			'right'=> (isset($params->cols['right'])?$params->cols['right']:'rgt'),
			'parent'=> (isset($params->cols['parent'])?$params->cols['parent']:'parent_id'),
			'name'=> (isset($params->cols['name'])?$params->cols['name']:'name')
		);
	}
	public function Get($table, $parentId, $position, $params=array()) {
		$cols = $this->Get_cols($params);
		$r = $this->prepare("SELECT {$cols['left']},{$cols['right']} FROM {$this->db_prefix}".$table." WHERE $parentIdCol=? LIMIT 1");
		$s = $r->execute(array($parentId));
		//positions
		return array(
			'parent'=> $parent,
			'left'=> $left,
			'right'=> $right
		);
		//check methods of getting values from PC_gallery->Move_category();
	}
	public function Insert($table, $parentId=0, $position=0, $data=array(), &$params=array()) {
		$this->core->Init_params($params);
		$cols = $this->Get_cols($params);
		
		//parent is top level
		if ($parentId == 0) {
			//the last node in the tree
			if ($position == 0) {
				$r = $this->query("SELECT max({$cols['right']}) FROM {$this->db_prefix}$table");
				if (!$r) {
					$params->errors->Add('database', 'select');
					return false;
				}
				$moveAfter = $r->fetchColumn();
			}
			//first node
			elseif ($position == -1) {
				$params->errors->Add('position', 'Temporary unavailable');
				return false;
				//$moveAfter = 0;
			}
			else {
				$params->errors->Add('position', 'Temporary unavailable');
				return false;
				//in the defined position
				/*$r = $this->prepare("SELECT {$cols['left']} FROM {$this->db_prefix}$table WHERE {$cols['parent']}=0 ORDER BY {$cols['left']} LIMIT ".($position-1).",1");
				$s = $r->execute(array());
				if (!$s) {
					$params->errors->Add('database', 'select');
					return false;
				}
				if ($r->rowCount() == 1) {
					$moveAfter = $r->fetchColumn()-1;
				}
				else {
					$r = $this->query("SELECT max({$cols['right']}) FROM {$this->db_prefix}$table");
					if (!$r) {
						$params->errors->Add('database', 'select');
						return false;
					}
					$moveAfter = $r->fetchColumn();
				}
				*/
			}
		}
		else {
			$r = $this->prepare("SELECT c.{$cols['left']},c.{$cols['right']} FROM {$this->db_prefix}$table c WHERE c.id=? GROUP BY c.id,c.{$cols['left']},c.{$cols['right']} LIMIT 1");
			$s = $r->execute(array($parentId));
			if (!$s) {
				$params->errors->Add('database', 'select');
				return false;
			}
			$parent = $r->fetch();
			if ($parent[$cols['right']]-$parent[$cols['left']] == 1) {
				$moveAfter = $parent[$cols['left']];
			}
			else {
				if ($position == 0) {
					$r = $this->prepare("SELECT max({$cols['right']}) FROM {$this->db_prefix}$table WHERE {$cols['left']} between ? and ?");
					$s = $r->execute(array(($parent[$cols['left']]+1), ($parent[$cols['right']]-1)));
					if (!$s) {
						$params->errors->Add('database', 'select');
						return false;
					}
					$moveAfter = $r->fetchColumn();
				}
				elseif ($position == -1) {
					$moveAfter = $parent[$cols['left']];
				}
				else {
					$r = $this->prepare("SELECT {$cols['left']} FROM {$this->db_prefix}$table WHERE {$cols['parent']}=? ORDER BY {$cols['left']} LIMIT ".($position-1).",1");
					$s = $r->execute(array($parentId));
					if (!$s) {
						$params->errors->Add('database', 'select');
						return false;
					}
					if ($r->rowCount() == 1) {
						$moveAfter = $r->fetchColumn()-1;
					}
					else {
						$moveAfter = $parent[$cols['right']]-1;
					}
				}
			}
		}
		
		//update left values
		$rUpdateLeft = $this->prepare("UPDATE {$this->db_prefix}$table SET {$cols['left']}={$cols['left']}+? WHERE {$cols['left']}>?");
		//create the gap for the node to move in
		$rUpdateLeft->execute(array(2, $moveAfter));
		if (!$rUpdateLeft) {
			$params->errors->Add('database', "Update `{$cols['left']}` values");
			return false;
		}
		
		//update right values
		$rUpdateRight = $this->prepare("UPDATE {$this->db_prefix}$table SET {$cols['right']}={$cols['right']}+? WHERE {$cols['right']}>?");
		$rUpdateRight->execute(array(2, $moveAfter));
		if (!$rUpdateRight) {
			$rUpdateLeft->execute(array(-2, $moveAfter));
			$params->errors->Add('database', "Update `{$cols['right']}` values");
			return false;
		}
		
		//create category
		$left = $moveAfter + 1;
		$right = $left + 1;
		
		$insert = array();
		$insert[$cols['parent']] = $parentId;
		$insert[$cols['left']] = $left;
		$insert[$cols['right']] = $right;
		
		if (is_array($params->data)) $insert += $params->data;
		
		$r = $this->prepare("INSERT INTO {$this->db_prefix}$table (".implode(',', array_keys($insert)).") VALUES(".implode(',', array_fill(0, count($insert), '?')).")");
		$s = $r->execute(array_values($insert));
		if (!$s) {
			$rUpdateLeft->execute(array(-2, $moveAfter));
			$rUpdateRight->execute(array(-2, $moveAfter));
			$params->errors->Add('database', 'Insert new node');
			return false;
		}
		$id = $this->db->lastInsertId($this->sql_parser->Get_sequence($table));
		return $id;
	}
	public function Move($table, $id, $parentId, $position=0) {
		$r = $this->prepare("UPDATE {$this->db_prefix}$table SET a=a WHERE b=b");
	}
	public function Debug($table, $params=array()) {
		$this->core->Init_params($params);
		$cols = $this->Get_cols($params);
		echo '<style type="text/css">'
			.'.pc_tree_debug{float:left;margin:4px;padding:17px 20px;background:#fffde0;color:#000000;border:1px dashed #e5e09b;font-family:Arial;font-size:13px;line-height:0.6cm;color:#003562;}'
			.'.pc_tree_debug span{font-size:8pt;color:#448CCB;}'
			.'.pc_tree_debug span.id{font-size:6pt;color:#aaa;}'
			.'</style>';
		$r = $this->query("SELECT * FROM {$this->db_prefix}$table ORDER BY {$cols['left']}");
		if (!$r) return;
		echo "<pre class=\"pc_tree_debug\">\n";
		$level = 0;
		while ($d = $r->fetch()) {
			if (isset($previous))
			if ($d[$cols['left']] < $previous[$cols['right']]) {
				$level++;
			}
			elseif ($d[$cols['left']]-1 != $previous[$cols['right']] && $d[$cols['right']]+1 != $previous[$cols['right']]) {
				$level -= $d[$cols['left']]-1-$previous[$cols['right']];
			}
			echo str_repeat(".......\t", $level);
			echo ' <span class="id">'.$d[$cols['id']].' ('.$d[$cols['parent']].')</span> '
				.(isset($d[$cols['name']])?PC_translit($d[$cols['name']]):'no name')
				.' <span>'.$d[$cols['left']].','.$d[$cols['right']].'</span>'
				."\n";
			$previous = $d;
		}
		echo '</pre>';
	}
}