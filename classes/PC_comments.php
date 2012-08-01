<?php
final class PC_comments extends PC_base {
	private $subject, $subject_id;
	public function Init($subject, $subject_id=null) {
		$this->subject = $subject;
		if (!empty($subject_id)) $this->subject_id = $subject_id;
	}
	public function Get_count($subject_id=null) {
		if (empty($subject_id)) $subject_id = $this->subject_id;
		if (empty($subject_id)) return false;
		$r = $this->prepare("SELECT count(*) FROM {$this->db_prefix}comments WHERE subject=? and subject_id=? and confirmed=1");
		$s = $r->execute(array($this->subject, $subject_id));
		if (!$s) return false;
		return $r->fetchColumn();
	}
	public function Get($subject_id=null, $params=array()) {
		if (empty($subject_id)) $subject_id = $this->subject_id;
		if (empty($subject_id)) return false;
		if (isset($params['paging'])) if ($params['paging']['pi']) {
			$limit = " LIMIT ".$params['paging']['cpp']." OFFSET ".(($params['paging']['pi']-1)*$params['paging']['cpp']);
		}
		else $limit = '';
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}comments WHERE subject=? and subject_id=? and confirmed=1 ORDER BY date ". (v($params["ascending_order"])?"ASC":"DESC") . $limit);
		$s = $r->execute(array($this->subject, $subject_id));
		if (!$s) return false;
		$comments = array();
		while ($d = $r->fetch()) {
			$comments[] = $d;
		}
		return $comments;
	}
	public function Add($author, $email, $comment, $subject_id=null, $checksum=null, $confirmed=0) {
		if (empty($subject_id)) $subject_id = $this->subject_id;
		if (empty($checksum)) $checksum = md5($author.chr(0).$comment.chr(0).$subject_id);
		
		$max_comment_body_length = intval($this->core->Get_variable("max_comment_body_length"));
		if( !$max_comment_body_length ) $max_comment_body_length = 3000;
		
		if (empty($subject_id)) {
			$r['errors'][] = 'subject_id';
		}
		if (!Validate('name', $author)) {
			$r['errors'][] = 'author';
		}
		if (!is_null($email) && !Validate('email', $email)) {
			$r['errors'][] = 'email';
		}
		if (mb_strlen($comment) < 2 || mb_strlen($comment) > 3000) {
			$r['errors'][] = 'comment';
		}
		if (count(v($r['errors'])) != 0) return $r;
		
		// check if such comment from this author in this topic already exists
		$dbs = $this->prepare("SELECT id FROM {$this->db_prefix}comments WHERE checksum=? LIMIT 1");
		$dbs->execute(Array($checksum));
		if( $id = $dbs->fetchColumn() )
			return array('success'=> true, 'id'=> $id, 'nomail'=>true);
		
		$r = $this->prepare("INSERT INTO {$this->db_prefix}comments (subject,subject_id,author,email,comment,date,ip,confirmed,checksum) values(?,?,?,?,?,?,?,?,?)");
		$s = $r->execute(array($this->subject, $subject_id, $author, $email, htmlspecialchars($comment), time(), $_SERVER['REMOTE_ADDR'], $confirmed, $checksum));
		if (!$s) return array('errors'=>array('database'));
		return array('success'=> true, 'id'=> $this->db->lastInsertId($this->sql_parser->Get_sequence('comments')));
	}
	public function Delete($ids) {
		if (!is_array($ids)) {
			if (empty($ids)) return false;
			$ids = array($ids);
		}
		$r = $this->prepare("DELETE FROM {$this->db_prefix}comments WHERE id in(".implode(',', $ids).")");
		$s = $r->execute();
		if (!$s) return false;
		return true;
	}
	public function Confirm($ids, $confirm=1) {
		if (!is_array($ids)) {
			if (empty($ids)) return false;
			$ids = array($ids);
		}
		$r = $this->prepare("UPDATE {$this->db_prefix}comments SET confirmed=? WHERE id in(".implode(',', $ids).")");
		$s = $r->execute(array($confirm));
		if (!$s) return false;
		return true;
	}

	/** Parses bbcode tags and converts them to html
	* @return string Returns HTML after parsing text
	*/
	static function Parse_bbcodes($text) {
		$tags = Array();
		$no_close_tags = Array("img");
		$level = 0;
		$offset = 0;
		$replaces = Array();
		while( preg_match('#\\[(/?(?:quote))([^\\]]*)\\]#is', $text, $mtc, PREG_OFFSET_CAPTURE, $offset) ) {
			$tag = strtolower($mtc[1][0]);
			$params = $mtc[2][0];
			$pos = $mtc[0][1];
			$len = strlen($mtc[0][0]);
			$offset = $mtc[0][1] + $len;
			$replace_id = count($replaces);
			
			// by default just remove the encountered bbcode tag
			$rep = "";
			$replaces[$replace_id] = Array($pos, $len, "");
			
			if( $tag[0] == "/" ) {
				if( $level < 1 ) {
					// display error message that no closing tag expected
					continue;
				}
				else if( $tags[$level][0] != substr($tag, 1) ) { // check if oppening tag for current level is the same as closing tag
					// display error message that different closing tag is expected
					$replaces[$tags[$level][1]][2] = ""; // since oppening tag does not match we have to remove it as well as closing tag
					continue;
				}
				else { // closing tag matches opening tag
					$opener_tag = $tags[$level];
					$level--;
				}
			}
			else {
				if( !in_array($tag, $no_close_tags) ) {
					$level++;
					$tags[$level] = Array($tag, $replace_id, $params);
				}
			}

			// for closing tags $opener_tag array is available. it contains tag_name([0]), tag_replacement_id([1]) and tag_parameters([2])
			// DO ALL THE REPLACEMENT PARSING HERE
			switch($tag) {
				case "/quote": $rep = "</span>"; break;
				case "quote": {
					if( preg_match('#name=(.*)$#iu', $params, $pmtc) )
						$rep = '<span class="quote"><span class="quote-author">' . $pmtc[1] . '</span>';
					else
						$rep = '<span class="quote">';
					break;
				}
			}
			
			$replaces[$replace_id][2] = $rep;
		}
		
		// automatically close all unclosed tags at the end
		$tlen = strlen($text);
		while($level > 0) {
			$opener_tag = $tags[$level];
			$level--;
			$rep = "";
			// DO ALL THE REPLACEMENT PARSING HERE
			switch($opener_tag[0]) {
				case "quote": $rep = "</span>"; break;
			}
			if( $rep )
				$replaces[] = Array($tlen, 0, $rep);
		}
		// print_pre($replaces);
		
		$reverse = array_reverse($replaces);
		foreach($reverse as $repinfo)
			//$text = substr($text, 0, $repinfo[0]) . $repinfo[2] . substr($text, $repinfo[0] + $repinfo[1]);
			$text = rtrim(substr($text, 0, $repinfo[0])) . $repinfo[2] . trim(substr($text, $repinfo[0] + $repinfo[1]));

		return $text;
	}
}