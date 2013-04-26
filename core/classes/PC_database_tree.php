<?php
final class PC_database_tree extends PC_base {
	public function Get_cols(&$params) {
		return array(
			'id'=> (!empty($params->cols['id'])?$params->cols['id']:'id'),
			'pid'=> (!empty($params->cols['pid'])?$params->cols['pid']:'pid'),
			'left'=> (!empty($params->cols['left'])?$params->cols['left']:'lft'),
			'right'=> (!empty($params->cols['right'])?$params->cols['right']:'rgt'),
			'parent'=> (!empty($params->cols['parent'])?$params->cols['parent']:'parent_id'),
			'name'=> (!empty($params->cols['name'])?$params->cols['name']:'name')
		);
	}
	public function Get($table, $parentId, $position=null, &$params=array()) {
		$this->core->Init_params($params);
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
	public function Move($table, $id, $parentId, $position=0, &$params=array()) {
		$this->core->Init_params($params);
		$cols = $this->Get_cols($params);
		//select category to move
		$rCategory = $this->prepare("SELECT {$cols['left']},{$cols['right']},{$cols['parent']} FROM {$this->db_prefix}{$table} WHERE id=? LIMIT 1");
		$s = $rCategory->execute(array($id));
		if (!$s) return false;
		if (!$rCategory->rowCount()) return false;
		$c = $rCategory->fetch();
		//calculate the gap
		$gap = $c['rgt']-$c['lft']+1;
		//get all subchild ids
		$rSub = $this->prepare("SELECT id FROM {$this->db_prefix}{$table} WHERE lft BETWEEN ? and ?");
		$s = $rSub->execute(array($c['lft'], $c['rgt']));
		if (!$s) return false;
		$subIds = array();
		while ($subId = $rSub->fetchColumn()) $subIds[] = $subId;
		unset($subId, $rSub);
		//get anchor side value
		$anchor = $this->Get_anchor($table, $parentId, $position, $params);
		if ($anchor === false) return false;
		//check if not trying to move category inside itself
		if ($anchor > $c[$cols['left']] && $anchor < $c[$cols['right']]) return false;
		//calculate difference
		$difference = $anchor - $c['lft'] + 1;
		if ($difference == 0) return true;
		//create gap to move in
		$queryParams = array_merge(array($gap, $anchor), $subIds);
		$rUpdateLeft = $this->prepare("UPDATE {$this->db_prefix}{$table} SET lft=lft+? WHERE lft>? AND id not ".$this->sql_parser->in($subIds));
		$rUpdateLeft->execute($queryParams);
		$rUpdateRight = $this->prepare("UPDATE {$this->db_prefix}{$table} SET rgt=rgt+? WHERE rgt>? AND id not ".$this->sql_parser->in($subIds));
		$rUpdateRight->execute($queryParams);
		//move nodes by difference
		$rMove = $this->prepare("UPDATE {$this->db_prefix}{$table} SET {$cols['left']}={$cols['left']}+?, {$cols['right']}={$cols['right']}+? WHERE {$cols['id']} ".$this->sql_parser->in($subIds));
		$rMove->execute(array_merge(array($difference, $difference), $subIds));
		//update parent id
		if ($c[$cols['parent']] != $parentId) {
			$this->prepare("UPDATE {$this->db_prefix}{$table} SET {$cols['parent']}=? WHERE {$cols['id']}=?")->execute(array($parentId, $id));
		}
		//delete gap left after moving nodes from it
		$rUpdateLeft = $this->prepare("UPDATE {$this->db_prefix}{$table} SET lft=lft-? WHERE lft>?");
		$rUpdateLeft->execute(array($gap, $c['rgt']));
		$rUpdateRight = $this->prepare("UPDATE {$this->db_prefix}{$table} SET rgt=rgt-? WHERE rgt>?");
		$rUpdateRight->execute(array($gap, $c['rgt']));
		return true;
	}
	public function Debug_tree($table, &$params=array()) {
		$orig_params = $params;
		$this->core->Init_params($params);
		$cols = $this->Get_cols($params);
		echo '<style type="text/css">'
			.'.pc_tree_debug{float:left;margin:4px;padding:17px 20px;background:#fffde0;color:#000000;border:1px dashed #e5e09b;font-family:Arial;font-size:13px;line-height:0.6cm;color:#003562;}'
			.'.pc_tree_debug span{font-size:8pt;color:#448CCB;}'
			.'.pc_tree_debug span.id{font-size:6pt;color:#aaa;}'
			.'</style>';
		
		$select = 't.*';
		$join = '';
		if (isset($orig_params['cols']['join_table']) and isset($orig_params['cols']['join_col'])) {
			$select .= ', tt.' . $cols['name'];
			$join = "LEFT JOIN {$this->db_prefix}{$orig_params['cols']['join_table']} tt ON tt.{$orig_params['cols']['join_col']} = t.{$cols['id']} AND tt.ln = 'lt'";
		}
		$query = "SELECT $select FROM {$this->db_prefix}$table t $join ORDER BY {$cols['left']}";
		$r = $this->query($query);
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
			echo ' <span class="id">id: '.$d[$cols['id']].' (parent_id: '.$d[$cols['parent']].')</span> '
				.(isset($d[$cols['name']])?PC_translit($d[$cols['name']]):'no name')
				.' <span>'.$d[$cols['left']].','.$d[$cols['right']].'</span>'
				."\n";
			$previous = $d;
		}
		echo '</pre>';
	}
	public function Get_anchor($table, $parentId=0, $position=0, &$params=array()) {
		$this->debug("Get_anchor($table, $parentId, $position)", 1);
		$this->core->Init_params($params);
		$cols = $this->Get_cols($params);
		
		if ($params->Get('position_is_anchor_leaf')) {
			$r = $this->prepare("SELECT {$cols['right']} FROM {$this->db_prefix}{$table} WHERE {$cols['id']}=? and {$cols['parent']}=? LIMIT 1");
			$s = $r->execute(array($position, $parentId));
			if (!$s) return false;
			return $r->fetchColumn();
		}
		
		//parent is top level
		if ($parentId == 0) {
			//the last node in the tree
			if ($position == 0) {
				$r = $this->query("SELECT max({$cols['right']}) FROM {$this->db_prefix}$table");
				if (!$r) {
					$params->errors->Add('database', 'select');
					return false;
				}
				$anchor = $r->fetchColumn();
				$this->debug("Max right:" . $anchor, 2);
				
			}
			//first node
			elseif ($position == -1) {
				$anchor = 0;
				$this->debug("Position -1:" . $anchor, 2);
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
					$anchor = $r->fetchColumn()-1;
				}
				else {
					$r = $this->query("SELECT max({$cols['right']}) FROM {$this->db_prefix}$table");
					if (!$r) {
						$params->errors->Add('database', 'select');
						return false;
					}
					$anchor = $r->fetchColumn();
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
				$anchor = $parent[$cols['left']];
				$this->debug("(Parent leaf) Parent left:" . $anchor, 2);
			}
			else {
				if ($position == 0) {
					$r = $this->prepare("SELECT max({$cols['right']}) FROM {$this->db_prefix}$table WHERE {$cols['left']} between ? and ?");
					$s = $r->execute(array(($parent[$cols['left']]+1), ($parent[$cols['right']]-1)));
					if (!$s) {
						$params->errors->Add('database', 'select');
						return false;
					}
					$anchor = $r->fetchColumn();
					$this->debug("Max right (between)" . $anchor, 2);
				}
				elseif ($position == -1) {
					$anchor = $parent[$cols['left']];
					$this->debug("(Parent not leaf) Parent left" . $anchor, 2);
				}
				else {
					$r = $this->prepare("SELECT {$cols['left']} FROM {$this->db_prefix}$table WHERE {$cols['parent']}=? ORDER BY {$cols['left']} LIMIT ".($position-1).",1");
					$s = $r->execute(array($parentId));
					if (!$s) {
						$params->errors->Add('database', 'select');
						return false;
					}
					if ($r->rowCount() == 1) {
						$anchor = $r->fetchColumn()-1;
						$this->debug("Row count is one, left: " . $anchor, 2);
					}
					else {
						$anchor = $parent[$cols['right']]-1;
						$this->debug("Max right - 1: " . $anchor, 2);
					}
				}
			}
		}
		if (is_null($anchor)) $anchor = 0;
		return $anchor;
	}
	public function Create_gap($table, $afterLeft) {
		$this->debug("Create_gap($table, $afterLeft)", 3);
		//update left values
		$query = "UPDATE {$this->db_prefix}$table SET lft=lft+? WHERE lft>?";
		$rUpdateLeft = $this->prepare($query);
		//create the gap for the node to move in
		$query_params = array(2, $afterLeft);
		$this->debug_query($query, $query_params, 4);
		$rUpdateLeft->execute($query_params);
		if (!$rUpdateLeft) return false;
		//update right values
		$query = "UPDATE {$this->db_prefix}$table SET rgt=rgt+? WHERE rgt>?";
		$rUpdateRight = $this->prepare($query);
		$query_params = array(2, $afterLeft);
		$this->debug_query($query, $query_params, 4);
		$rUpdateRight->execute($query_params);
		if (!$rUpdateRight) {
			$query_params = array(-2, $afterLeft);
			$this->debug_query($query, $query_params, 5);
			$rUpdateLeft->execute($query_params);
			return false;
		}
		return true;
	}
	public function Delete_gap($table, $afterLeft, $gap=2) {
		$this->debug("Delete_gap($table, $afterLeft, $gap)", 3);
		
		$query = "DELETE FROM {$this->db_prefix}$table WHERE lft>? AND rgt<?";
		$rUpdateLeft = $this->prepare($query);
		$query_params = array($afterLeft + 1, $afterLeft + $gap);
		$this->debug_query($query, $query_params, 4);
		$rUpdateLeft->execute($query_params);
		
		$query = "UPDATE {$this->db_prefix}$table SET lft=lft-? WHERE lft>?";
		$rUpdateLeft = $this->prepare($query);
		$query_params = array((int)$gap, $afterLeft);
		$this->debug_query($query, $query_params, 4);
		$rUpdateLeft->execute($query_params);
		
		$query = "UPDATE {$this->db_prefix}$table SET rgt=rgt-? WHERE rgt>?";
		$rUpdateRight = $this->prepare($query);
		$query_params = array((int)$gap, $afterLeft);
		$this->debug_query($query, $query_params, 4);
		$rUpdateRight->execute($query_params);
		return true;
	}
	
	protected function _get_all($table, $cols, $where = '', $order = false) {
		//$rList = $this->query("SELECT {$cols['pid']},{$cols['parent']},{$cols['id']} FROM {$this->db_prefix}".$table." ORDER BY {$cols['pid']} DESC, {$cols['parent']},{$cols['id']}");
		if (!empty($where)) {
			$where = " WHERE $where ";
		}
		$order_s = ' ORDER by ' . $cols['pid'] . ' ';
		if ($order) {
			$order_s = ' ORDER by ' . $cols['left'] . ' ';
		}
		$query = "SELECT {$cols['pid']},{$cols['parent']},{$cols['id']} FROM {$this->db_prefix}".$table. $where . $order_s;
		$this->debug($query);
		//echo '<hr />' . $query;
		$rList = $this->query($query);
		if ($rList) {
			while($d = $rList->fetch()) {
				$this->dd[$d[$cols['id']]] = $d;
				$this->_get_all($table, $cols, "{$cols['parent']} = " . $d[$cols['id']], true);
			}
		}
		
	}
	
	public function Recalculate($table, &$params=array()) {
		$this->debug = true;
		$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'tree_recalculate.html');
		$this->core->Init_params($params);
		$cols = $this->Get_cols($params);
		//escape $table first!
		$s = $this->query("UPDATE {$this->db_prefix}".$table." SET {$cols['left']}=0, {$cols['right']}=0");
		if (!$s) return false;
		$this->_update_query = $update_query = "UPDATE {$this->db_prefix}".$table." SET {$cols['left']}=?, {$cols['right']}=? WHERE {$cols['id']}=?";
		$rUpdate = $this->prepare($this->_update_query);
		$this->dd = array();
		$this->_get_all($table, $cols, "{$cols['pid']} != 0");
		//print_pre($this->dd);
		//exit;
		$this->anchors = array();
		foreach ($this->dd as $d) {
			$this->_recalculate_item($table, $cols, $d, $rUpdate);
		}
		return;
		$fantoms = array();
		$query = "SELECT {$cols['pid']},{$cols['parent']},{$cols['id']} FROM {$this->db_prefix}".$table. " WHERE lft = 0 AND rgt = 0 ORDER by {$cols['id']}";
		//echo '<hr />' . $query;
		$rList = $this->query($query);
		if ($rList) {
			while($d = $rList->fetch()) {
				$this->dd[$d[$cols['id']]] = $d;
				$this->_get_all($table, $cols, "{$cols['parent']} = " . $d[$cols['id']], true);
			}
		}
		
		
	}
	
	protected function _recalculate_item($table, $cols, $d, $rUpdate) {
		if (isset($this->anchors[$d[$cols['id']]])) {
			$this->debug(':( no anchor', 2);
			return;
		}
		$my_parent_id = $d[$cols['parent']];
		if ($my_parent_id != 0 and !isset($this->anchors[$my_parent_id])) {
			$this->_recalculate_item($table, $cols, $this->dd[$my_parent_id], $rUpdate);
		}
		$this->debug($d, 1);
		$setAfter = $this->Get_anchor($table, $d[$cols['parent']]);
		$this->debug("setAfter: $setAfter ", 1);
		$this->anchors[$d[$cols['id']]] = $setAfter;
		if ($setAfter === false) return;
		if ($this->Create_gap($table, $setAfter)) {
			$query_params = array($setAfter+1, $setAfter+2, $d[$cols['id']]);
			$this->debug_query($this->_update_query, $query_params, 2);
			$s = $rUpdate->execute($query_params);
			if (!$s) {
				$this->Delete_gap($table, $setAfter);
			}
		}
	}
	
	public static function remove_inner_ranges($ranges) {
		$good_ranges = array();
		foreach ($ranges as $key => $range) {
			$add_range = true;
			foreach ($good_ranges as $k => $good_range) {
				if ($good_range['lft'] <= $range['lft'] and $good_range['rgt'] >= $range['rgt']) {
					$add_range = false;
					break;
				}
			}
			if ($add_range) {
				$good_ranges[$range['lft']] = array(
					'lft' => $range['lft'],
					'rgt' => $range['rgt']
				);
			}
		}
		return $good_ranges;
	}
	
	public static function get_between_condition(&$ranges, &$query_params = false, $table = '') {
		if (!empty($table)) {
			$table .= '.';
		}
		$betweens = array();
		foreach ($ranges as $range) {
			if ($range)
			if ($query_params !== false) {
				$betweens[] = "({$table}lft BETWEEN ? AND ?)";
				$query_params[] = $range['lft'];
				$query_params[] = $range['rgt'];
			}
			else {
				$betweens[] = "({$table}lft BETWEEN {$range['lft']} AND {$range['rgt']})";
			}
		}
		$between_cond = '';
		if (!empty($betweens)) {
			$between_cond = '(' . implode(' OR ', $betweens) . ')';
		}
		return $between_cond;
	}
	
	public static function get_between_condition_for_range(&$range, &$query_params, $table = '') {
		if (!empty($table)) {
			$table .= '.';
		}
		$between_cond = "({$table}lft BETWEEN ? AND ?)";
		$query_params[] = $range['lft'];
		$query_params[] = $range['rgt'];
		return $between_cond;
	}
	
}