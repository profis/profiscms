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

final class PC_page extends PC_base {
	public  $text,
			$data,
			$menus = array();
	private $_menu_shift = 1,
			$_gmap_counter = 0,
			$_media_counter = 1;
	public function Init() {
		//constructor
	}
	public function Get_route_data($route=null, $route_is_page_id=false, $path=array(), $internal_redirects=true) {
		$now = time();
		$r = $this->prepare("SELECT p.date,p.front,p.id pid,p.idp,c.*,p.controller,p.redirect,h.id redirect_from_home,p.nr,p.reference_id,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'routes.ln', 'routes.route'), array('separator'=>'▓'))." routes"
		.(is_null($route)?
			" FROM {$this->db_prefix}pages p JOIN {$this->db_prefix}content c ON pid=p.id"
			:" FROM {$this->db_prefix}content c JOIN {$this->db_prefix}pages p ON p.id=pid"
		)
		/*." FROM {$this->db_prefix}content c"
		." JOIN {$this->db_prefix}pages p ON p.id=pid"*/
		//check if home page redirects to this page
		." LEFT JOIN {$this->db_prefix}pages h ON h.front=1 and h.redirect=".$this->sql_parser->cast('p.id', 'text')
		." LEFT JOIN {$this->db_prefix}content routes ON routes.pid=p.id"
		." WHERE ".(is_null($route)?"p.front>0":($route_is_page_id?"p.id":"c.route")."=? ")
		." and p.site=? and p.deleted=0 and p.published=1 and (p.date_from is null or p.date_from<=?) and (p.date_to is null or p.date_to>=?)"
		." and c.ln=?"
		." GROUP BY ".$this->sql_parser->group_by('p.id,p.front,p.idp,p.controller,p.redirect,redirect_from_home,p.nr,c.id,c.pid,c.ln,c.name,c.info,c.info2,c.info3,c.title,c.keywords,c.description,c.route,c.text,c.last_update,c.update_by,p.date')
		." LIMIT 1");
		$params = array($this->site->data['id'], $now, $now, $this->site->ln);
		if (!is_null($route)) array_unshift($params, $route);
		$success = $r->execute($params);
		if (!$success) {
			return array('controller'=>'core','data'=>'database_error');
		}
		if ($r->rowCount() != 1) {
			return array('controller'=>'core','data'=>404);
		}
		$data = $r->fetch();
		$this->core->Parse_data_str($data['routes'], '▓', '░');
		$this->Parse_html_output($data['text'], $data['info'], $data['info2'], $data['info3']);
		$this->Parse_html_output($data['description'], $data['keywords'], $data['title']);
		//save route path
		if (!is_array($path)) $path = array();
		$path[] = $data;
		$data['path'] =& $path;
		//do not load page that is pointed by home directly
		if (!$route_is_page_id) if (isset($data['redirect_from_home'])) {
			return array('controller'=>'core','data'=>404);
		}
		if (!empty($route)) {
			if ($data['front']) return array('controller'=>'core','data'=>404);
		}
		if (!empty($data['redirect'])) {
			if (preg_match("#^http://#", $data['redirect'])) {
				$data = array(
					'controller'=> 'core',
					'action'=> 'http_redirect',
					'data'=> array(
						'url'=> $data['redirect'],
						'page'=> $data
					)
				);
				return $data;
			}
			//parse redirect data
			$d = $this->Parse_redirect_data($data['redirect']);
			//shouldnt redirects be done internally?
			if (!$internal_redirects && !$data['front']) {
				//print_pre($d); exit();
				$url = $this->cfg['url']['base'];
				if (!empty($d['pid'])) {
					$redirect_page = $this->Get_page($d['pid']);
					$url .= $this->site->Get_link($redirect_page['route']);
					$url .= $this->routes->Get_range(2); //all except first
				}
				else {
					$url .= $this->routes->Get_request();
				}
				if (!empty($d['get'])) {
					$url .= '?'.$d['get'];
				}
				else if (!empty($this->routes->get_request)) {
					$url .= '?'.$this->routes->get_request;
				}
				//if ($d['get'] != $this->routes->get_request) {}
				$this->core->Redirect($url, 301);
			}
			else return $this->Get_route_data($d['pid'], true);
		}
		if (empty($data['controller']) || $data['controller'] == 'menu') $data['controller'] = 'page';
		
		return $data;
	}
	public function Parse_redirect_data($redirect) {
		if (strpos($redirect, '#') !== false) {
			$d = explode('#', $redirect);
			$redirect = $d[0];
			$hash = $d[1];
		}
		if (strpos($redirect, '?') !== false) {
			$d = explode('?', $redirect);
			$redirect = $d[0];
			$get = $d[1];
		}
		$redirect = array(
			'pid' => v($redirect),
			'get' => v($get),
			'hash' => v($hash)
		);
		return $redirect;
	}
	//single method that parses gallery file requests, replaces google maps objects, trims page break etc.
	public function Parse_html_output(&$t1, &$t2=null, &$t3=null, &$t4=null, &$t5=null) {
		$tc = 1;
		$var = 't'.$tc;
		$text =& $$var;
		while (isset($text)) {
			#
			$this->Parse_gallery_files_requests($text);
			$this->core->Init_hooks('parse_html_output', array(
				'text'=> &$text
			));
			$this->Replace_google_map_objects($text);
			$this->Replace_media_objects($text);
			//fix hash links
			if (isset($this->route[1])) $text = preg_replace("/href=\"(#[^\"]+)\"/ui", "href=\"".$this->site->Get_link($this->route[1])."$1\"",  $text);
			//append prefix to the links from editor
			if (isset($this->route[1])) $text = preg_replace("/href=\"/ui", "href=\"".$this->site->link_prefix,  $text);
			//remove default language code from links
			$text = preg_replace("/href=\"".$this->site->default_ln."\//", "href=\"",  $text);
			//page break
			$text = str_replace('╬', '<span style="display:none" id="pc_page_break">&nbsp;</span>', $text);
			//prevent bots from seeing raw email addresses
			$text = preg_replace_callback("#".$this->cfg['patterns']['email']."#i", array($this, 'Encode_email'), $text);
			#continue to the next arg
			$tc++;
			$var = 't'.$tc;
			$text =& $$var;
		}
	}
	public function Encode_emails($text) {
		$text = preg_replace_callback("#".$this->cfg['patterns']['email']."#i", array($this, 'Encode_email'), $text);
		return $text;
	}
	public function Encode_email($match) { //temp
		$link = (substr($match[0], 0, 7) == 'mailto:');
		return ($link?'mailto:':'').Hex_encode(($link?substr($match[0], 7):$match[0]), !$link);
	}
	public function Parse_gallery_files_requests_old(&$text) {
		if (!empty($text)) {
			preg_match_all('#(url\("?|")((gallery/admin/id/(thumb-)?([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9]/)?)([0-9]+)"\)?)#i', $text, $matches);
			if (count($matches[5])) {
				$r = $this->query("SELECT f.id,filename,"
				.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path
				FROM {$this->db_prefix}gallery_files f
				LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = category_id
				LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
				WHERE f.id in(".implode(',', $matches[5]).")
				GROUP BY f.id,f.filename");
				if ($r) {
					while ($data = $r->fetch()) {
						$this->gallery->Sort_path($data['path']);
						$data['path'] = !empty($data['path'])?$data['path'].'/':'';
						$files[$data['id']] = $data;
					}
					for ($a=0; isset($matches[0][$a]); $a++) {
						$to = '"gallery/'.$files[$matches[5][$a]]['path'].$matches[3][$a].$matches[4][$a].$files[$matches[5][$a]]['filename'].'"';
						$this->gallery_request_map[$files[$matches[5][$a]]['path'].$files[$matches[5][$a]]['filename']] = $matches[5][$a];
						$text = str_replace($matches[0][$a], $to, $text);
					}
				}
			}
		}
		return true;
	}
	public function Parse_gallery_files_requests(&$text) {
		if (!empty($text)) {
			//preg_match_all('#"((gallery/admin/id/(thumb-)?([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9]/)?)([0-9]+)")#i', $text, $matches);
			preg_match_all('#(url\("?|")((gallery/admin/id/(thumb-)?([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9]/)?)([0-9]+))("?\)|")#i', $text, $matches);
			if (count($matches[6])) {
				//print_pre($matches);
				$r = $this->query("SELECT f.id,filename,"
				.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path
				FROM {$this->db_prefix}gallery_files f
				LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = category_id
				LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
				WHERE f.id in(".implode(',', $matches[6]).")
				GROUP BY f.id,f.filename");
				if ($r) {
					while ($data = $r->fetch()) {
						$this->gallery->Sort_path($data['path']);
						$data['path'] = !empty($data['path'])?$data['path'].'/':'';
						$files[$data['id']] = $data;
					}
					//print_pre($files);
					for ($a=0; isset($matches[0][$a]); $a++) {
						$to = 'gallery/'.$files[$matches[6][$a]]['path'].$matches[4][$a].$matches[5][$a].$files[$matches[6][$a]]['filename'].'';
						$this->gallery_request_map[$files[$matches[6][$a]]['path'].$files[$matches[6][$a]]['filename']] = $matches[6][$a];
						//echo $to.'<br />';
						$text = preg_replace("#".$matches[2][$a]."([^0-9])#", $to."$1", $text, -1, $count);
						if (!$count) $text = str_replace($matches[2][$a], $to, $text);
					}
				}
			}
		}
		return true;
	}
	public function Replace_google_map_objects(&$text) {
		//<object width="100%" height="240" classid="clsid:google-map" codebase="http://maps.google.com/"> <param name="map_data" value="%7B%22latitude%22%3A55.710803%2C%22longitude%22%3A21.13180699999998%2C%22zoom%22%3A12%2C%22map_type%22%3A%22satellite%22%7D" /> <param name="src" value="maps.google.com" /><embed src="maps.google.com" type="application/google-map" width="100%" height="240px">&nbsp;</embed> </object>
		//<object width="500" height="240" classid="clsid:google-map" codebase="http://maps.google.com/"><param name="map_data" value="%7B%22latitude%22%3A43.635515820871454%2C%22longitude%22%3A51.17217413757328%2C%22zoom%22%3A15%2C%22map_type%22%3A%22hybrid%22%7D" /><param name="src" value="maps.google.com" /><embed src="maps.google.com" type="application/google-map" width="500" height="240">&nbsp;</embed></object>
		$google_map_object = '/<object( style="(.+?)")? width="([0-9]+[a-z%]*?)" height="([0-9]+[a-z%]*?)" classid="clsid:google-map" codebase=".+?">'."\s*".'<param name="map_data" value="(.+?)" \/>'."\s*".'<param name="src" value=".+?" \/><embed src=".+?" type="application\/google-map" width="[0-9]+[a-z%]*?" height="[0-9]+[a-z%]*?">.*?<\/embed>'."\s*".'<\/object>/miu';
		if (!empty($text)) if (preg_match_all($google_map_object, $text, $gmaps)) {
			$this->site->Add_script('http://maps.google.com/maps/api/js?sensor=false');
			for ($a=0; isset($gmaps[0][$a]); $a++) {
				$json_data = urldecode($gmaps[5][$a]);
				$data = json_decode($json_data);
				$id = 'gmap_'.$this->_gmap_counter++;
				$w = $gmaps[3][$a];
				$h = $gmaps[4][$a];
				$google_map_frame = '<div id="'.$id.'" class="google-map" style="width:'.$w.'px;height:'.$h.'px;'.$gmaps[2][$a].'">'
				.'<script type="text/javascript">'
					.'var options_'.$id.'={zoom:'.$data->zoom.',center:new google.maps.LatLng('.$data->latitude.','.$data->longitude.'),mapTypeId:google.maps.MapTypeId.'.strtoupper($data->map_type).',streetViewControl:false};'
					.'var '.$id.'=null;'
					.'$(function(){'
						.$id.'=new google.maps.Map(document.getElementById(\''.$id.'\'),options_'.$id.');'
						.'new google.maps.Marker({map:'.$id.',animation:google.maps.Animation.DROP,position: options_'.$id.'.center});'
					.'});'
				.'</script></div>';
				$text = preg_replace('#'.preg_quote($gmaps[0][$a], "#").'#m', $google_map_frame, $text, 1);
			}
		}
		return true;
	}
	public function Replace_media_objects(&$text) {
		//<object style="display: block; margin: 0px 0px 0px 0px;" width="425" height="349" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="src" value="gallery/admin/id/253" /><embed src="gallery/admin/id/253" type="application/x-shockwave-flash" width="425" height="349"></embed></object>
		$object = '/<object (style="(.*?)" )?width="([0-9]+[a-z%]*?)" height="([0-9]+[a-z%]*?)" classid=".+?" codebase=".+?">'
		.'\s*<param name="src" value="(.+?)" \/>'
		.'(\s*<param name="poster" value="(.+?)" \/>)?'
		.'(\s*<param name="skin" value="(.+?)" \/>)?'
		.'\s*<embed src=".+?" type="(.+?)" width="[0-9]+[a-z%]*?" height="[0-9]+[a-z%]*?">.*?<\/embed>'
		.'\s*<\/object>/miu';
		$r = preg_match_all($object, $text, $media);
		if ($r) {
			$base =& $this->cfg['url']['base'];
			for ($a=0; isset($media[0][$a]); $a++) {
				$id = 'pc_media_'.$this->_media_counter++;
				$style = $media[2][$a];
				$w = $media[3][$a];
				$h = $media[4][$a];
				$src = $media[5][$a];
				$poster = $media[7][$a];
				$skin = $media[9][$a];
				$media_frame = '<div class="pc_media_player" style="height:'.$h.'px;'.$style.'"><div id="'.$id.'">';
				//if ($media[10][$a] == 'application/x-shockwave-flash') {
				if (preg_match("#\.swf$#i", $src)) {
					$media_frame .= "<script type=\"text/javascript\">var params={id:'".$id."',wmode:'transparent',allowFullScreen:true,allowScriptAccess:'always'};"
					."new swfobject.embedSWF('".$base.$src."','".$id."', ".$w.", ".$h.",'9.0.115');</script>";
				}
				else {
					//jwplayer: '<script type="text/javascript">jwplayer("'.$id.'").setup({flashplayer:"media/jwplayer/player.swf",file:"'.$this->cfg['url']['base'].$src.'",height:'.$h.',width:'.$w.'});</script>'
					//uppod:
					$media_frame .= "<script type=\"text/javascript\">var flashvars={m:'video',uid:'".$id."',file:'".$base.$src."'".(!empty($poster)?",poster:'".$base.$poster."'":"")
					.(!empty($skin)?',st:\''.$base.$skin."'":'')."};"
					."var params={id:'".$id."',wmode:'transparent',allowFullScreen:true,allowScriptAccess:'always'};"
					."new swfobject.embedSWF('".$base."media/uppod/uppod.swf', '".$id."', ".$w.", ".$h.", '9.0.115', false, flashvars, params);</script>";
				}
				$media_frame .= '</div></div>';
				$text = preg_replace('#'.preg_quote($media[0][$a], '#').'#m', $media_frame, $text, 1);
			}
		}
		return true;
	}
	/*if only thing you have is page id, you can put it in place of $page array
	and this method would start by querying for this page data first*/
	public function Get_path($page, $cache=true, $parseLinks=true) {
		//get page id whenever $page is array or just id, we'll use it for cache manipulation
		if (is_array($page)) $pid = $page['pid'];
		else $pid = $page;
		//enable caching
		if ($cache) {
			$cached =& $this->cache->Get('page_pathes', $pid);
			if ($cached) return $cached;
		}
		//print_pre($page);
		//if defined $page is not array (is just page id), then we first need to get that page data
		if (!is_array($page)) $page = $this->Get_page($page, $parseLinks);
		//get full path with all ancestors included
		$path = array();
		while ($page) {
			//unshift assoc array
			//$path = array($page['pid'] => array($page['name'], $page['route'])) + $path;
			array_unshift($path, $page);
			//get parent
			$page = $this->Get_page(v($page['idp']), $parseLinks);
		}
		return $this->cache->Cache(array('page_pathes', $pid), $path);
	}
	public function Get_page($id=null, $parseLinks=true, $use_reference_id=false) {
		$where = array();
		$params = array(v($this->site->ln));
		if (!is_null($id)) {
			if (!$use_reference_id) {
				$where[] = "p.id=?";
				$where[] = "p.controller!='menu'";
			}
			else {
				$where[] = "reference_id=?";
			}
			$params[] = $id;
		}
		$r = $this->prepare("SELECT *"
		." FROM {$this->db_prefix}pages p"
		." LEFT JOIN {$this->db_prefix}content c ON c.pid=p.id and c.ln=?"
		.(count($where)?" WHERE ".implode(' and ', $where):""));
		$s = $r->execute($params);
		if (!$s) return false;
		if (!$r->rowCount()) return (is_null($id)?array():false);
		$list = array();
		while ($d = $r->fetch()) {
			if ($parseLinks) $this->Parse_html_output($d['text'], $d['info'], $d['info2'], $d['info3']);
			$list[] = $d;
		}
		return (is_null($id)?$list:$list[0]);
	}
	public function Load_menu() {
		$now = time();
		$r = $this->prepare("SELECT mp.id idp,c.pid,c.id cid,c.name,c.route,p.nr,p.hot,h.id redirect_from_home,p.controller,p.redirect,p.reference_id FROM {$this->db_prefix}pages mp"
		." LEFT JOIN {$this->db_prefix}pages p ON p.idp = mp.id"
		." AND p.controller!='menu' AND p.nomenu<1"
		." JOIN {$this->db_prefix}content c ON pid=p.id AND ln='{$this->site->ln}'"
		//check if home page rediects to this page
		." LEFT JOIN {$this->db_prefix}pages h ON h.front=1 and h.redirect=".$this->sql_parser->cast('p.id', 'text')
		." WHERE mp.controller='menu' and p.site=? and p.published=1 and p.deleted=0"
		." and (p.date_from is null or p.date_from<='$now') and (p.date_to is null or p.date_to>='$now')"
		." ORDER BY mp.nr,p.nr");
		$s = $r->execute(array($this->site->data['id']));
		if (!$s) return false;
		//if there's no menu, just leave empty menus array and return
		if ($r->rowCount() < 1) return true;
		//push all menus to the menus array and return
		$index = 0; $map = array();
		while ($menu = $r->fetch()) {
			if (!isset($map[$menu['idp']])) {
				$map[$menu['idp']] = $index;
				$index++;
			}
			if ($menu['pid'] == $this->page->data['pid']) {
				$menu['current'] = true;
			}
			if ($menu['redirect_from_home']) $menu['route'] = '';
			$this->menus[$map[$menu['idp']]][] = $menu;
		}
		return true;
	}
	public function Get_menu($shift=0) {
		//load menus if they're not loaded yet; return false if error occurs while loading;
		if (!is_array($this->menus) || !count($this->menus)) {
			if (!$this->Load_menu()) return false;
		}
		$total = count($this->menus);
		//if there's no menus - return empty list
		if (!$total) return array();
		//return requested menu
		if ($shift) {
			$shift = (int)$shift;
			if (isset($this->menus[$shift-1])) {
				return $this->menus[$shift-1];
			}
			else return array();
		}
		//rewind shifting for the menus
		if ($this->_menu_shift > $total) $this->_menu_shift = 1;
		//shift menu
		$menu = $this->menus[$this->_menu_shift-1];
		//update index of the menus shifted and return
		$this->_menu_shift++;
		return $menu;
	}
	public function Get_html_menu($shift=0) {
		$menu = $this->Get_menu($shift);
		$html = '<ul>';
		//print_pre($menu);
		foreach ($menu as $item) {
			//print_pre($item);
			$html .= '<li '.($item['hot']?'class="hot"':'').'><a href="'.$this->site->Get_link($item['route']).'">'.(!empty($item['name'])?$item['name']:'<i>#'.$item['pid'].'</i>').'</a>';
			//if this item is opened - submenu should be displayed
			if ($this->site->Is_opened($item['pid'])) {
				$submenu = $this->Get_submenu($item['pid'], array('pid','name','route','info'));
				$html .= '<ul style="padding-left:15px;">';
				foreach ($submenu as $item) {
					$hot = (isset($item['hot'])?($item['hot']?' class="hot"':''):'');
					$html .= '<li'.$hot.'><a '.($this->site->Is_opened($item['pid'])?'style="font-weight:bold;" ':'').'href="'.$this->site->Get_link($item['route']).'">'.(!empty($item['name'])?$item['name']:'<i>#'.$item['id'].'</i>').'</a></li>';
				}
				unset($hot);
				$html .= '</ul>';
			}
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;
	}
	public function Get_redirects_from($pid) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}pages WHERE redirect=?");
		$s = $r->execute(array($pid));
		if (!$s) return false;
		$ids = array();
		while ($id = $r->fetchColumn()) {
			$ids[] = $id;
		}
		return $ids;
	}
	//temporary hack, should be optimised by implementing modified preorder tree-traversal algorythm in the pages database
	public function Count_subpages($parent) {
		$now = time();
		$r = $this->prepare("SELECT count(p.id) total FROM {$this->db_prefix}pages p"
		." JOIN {$this->db_prefix}content c ON pid=p.id AND ln='{$this->site->ln}'"
		." WHERE controller!='menu' and nomenu=0 and deleted=0 and published=1"
		." and (date_from is null or date_from<='$now') and (date_to is null or date_to>='$now') and idp=?");
		$s = $r->execute(array($parent));
		if (!$s) return 0;
		return $r->fetchColumn();
	}
	public function Get_submenu($id, $fields=array(), $limit=false, $include_content=true, $include_nomenu=false) {
		//fields selection!
		$now = time();
		//retrieve only specified fields
		$fields_count = count($fields);
		if ($fields_count) {
			$valid_fields = array(
				'idp'=> 'mp.id idp',
				'pid'=> 'p.id pid',
				'cid'=> 'c.id cid',
				'name'=> 'c.name',
				'route'=> 'c.route',
				'nr'=> 'p.nr',
				'hot'=> 'p.hot',
				'info'=> 'c.info',
				'info2'=> 'c.info2',
				'info3'=> 'c.info3',
				'text'=> 'c.text',
				'title'=> 'c.title',
				'keywords'=> 'c.keywords',
				'description'=> 'c.description',
				'last_update'=> 'c.last_update',
				'update_by'=> 'c.update_by',
				'controller'=> 'p.controller',
				'front'=> 'p.front',
				'date_from'=> 'p.date_from',
				'date_to'=> 'p.date_to',
				'redirect'=> 'p.redirect',
				'date'=> 'p.date',
				'reference_id'=> 'p.reference_id',
				'nomenu'=> 'c.nomenu'
			);
			$retrieve_fields = '';
			for ($a=0; $a<$fields_count; $a++) {
				if (!isset($valid_fields[$fields[$a]])) {
					unset($fields[$a]);
				}
				else {
					if (!empty($retrieve_fields)) $retrieve_fields .= ',';
					$retrieve_fields .= $valid_fields[$fields[$a]];
				}
			}
		}
		$r = $this->prepare("SELECT ".(!empty($retrieve_fields)?$retrieve_fields:"mp.id idp,p.id pid".($include_content?",c.id cid,c.name,c.route":'').",p.nr,p.hot,p.date").","
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'h.id', 'h.front'), array('separator'=>'▓'))." redirects_from"
		." FROM {$this->db_prefix}pages mp"
		." LEFT JOIN {$this->db_prefix}pages p ON p.idp = mp.id"
		." AND p.controller!='menu' ".($include_nomenu?'':' AND p.nomenu=0')." AND p.deleted=0"
		.($include_content?" JOIN {$this->db_prefix}content c ON pid=p.id AND ln='{$this->site->ln}'":'')
		//check if home page rediects to this page
		." LEFT JOIN {$this->db_prefix}pages h ON h.redirect=".$this->sql_parser->cast('p.id', 'text')
		." WHERE mp.id ".(is_array($id)?'in('.implode(',', $id).')':'=?')." and p.published=1"
		." and (p.date_from is null or p.date_from<='$now') and (p.date_to is null or p.date_to>='$now')"
		." GROUP BY p.id"
		." ORDER BY mp.nr,p.nr".(($limit)?" limit $limit":""));
		$params = array();
		if (!is_array($id)) $params[] = $id;
		if (is_array($id) && !count($id)) return false;
		$success = $r->execute($params);
		if (!$success) return false;
		$items = array();
		if ($r->rowCount() >= 1) while ($menu = $r->fetch()) {
			if (isset($menu['pid'])) if ($menu['pid'] == v($this->site->loaded_page['pid'])) {
				$menu['current'] = true;
			}
			if (isset($menu['text'])) $this->Parse_html_output($menu['text']);
			if (isset($menu['info'])) $this->Parse_html_output($menu['info']);
			if (isset($menu['info2'])) $this->Parse_html_output($menu['info2']);
			if (isset($menu['info3'])) $this->Parse_html_output($menu['info3']);
			$this->core->Parse_data_str($menu['redirects_from'], '▓', '░');
			$menu['redirect_from_home'] = in_array(1, $menu['redirects_from']);
			$menu['redirects_from'] = array_keys($menu['redirects_from']);
			if ($menu['redirect_from_home']) $menu['route'] = '';
			$items[] = $menu;
		}
		return $items;
	}
	public function Get_subpages($id, $where='') {
		if (is_array($id) && !count($id)) return false;
		elseif (empty($id)) return false;
		$r = $this->prepare("SELECT p.id pid,p.*"
		." FROM {$this->db_prefix}pages mp"
		." LEFT JOIN {$this->db_prefix}pages p ON p.idp = mp.id"
		." WHERE mp.id ".(is_array($id)?'in('.implode(',', $id).')':'=?')
		.$where
		." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.hot,p.nomenu,p.published,p.deleted,p.route_lock,p.redirect,p.date_from,p.date_to,p.date,p.reference_id");
		$params = array();
		if (!is_array($id)) $params[] = $id;
		$s = $r->execute($params);
		if (!$s) return false;
		$items = array();
		if ($r->rowCount() >= 1) {
			while ($d = $r->fetch()) {
				if (is_null($d['pid'])) continue;
				$items[] = $d;
			}
		}
		return $items;
	}
	/*public function Process($data) {
		//page without controller shouldn't have subroutes, so only 1 route is required
		//$this->Set_required_routes(1);
		//if (Check_routes_count(1));
		/*$subroute = $this->Get_subroutes();
		if (!empty($subroute)) {
			$this->Call('core', 'Show_error', 404);
			return;
		}* /
		//replace objects (images, media etc) in text
		if (!empty($this->text)) {
			$this->Parse_gallery_files_requests($this->text);
			$this->Parse_gallery_files_requests($this->page['info']);
			$this->Parse_gallery_files_requests($this->page['info2']);
			$this->Parse_gallery_files_requests($this->page['info3']);
			$this->Replace_google_map_objects($this->text);
			$this->Replace_google_map_objects($this->page['info']);
			$this->Replace_google_map_objects($this->page['info2']);
			$this->Replace_google_map_objects($this->page['info3']);
			//fix anchors
			$this->text = preg_replace("/href=\"(#[^\"]+)\"/", "href=\"".$this->route[0]."$1\"",  $this->text);
		}
	}
	*/
	public function Get_text($pid) {
		$r = $this->prepare("SELECT text FROM {$this->db_prefix}content c WHERE pid=? and ln=? LIMIT 1");
		$success = $r->execute(array($pid, $this->site->ln));
		if ($success) if ($r->rowCount()) {
			$text = $r->fetchColumn();
			$this->Parse_html_output($text);
			return $text;
		}
	}
	public function Get_content($pid, $ln=null) {
		if (is_null($ln)) {
			$ln = $this->site->ln;
		}
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}content c WHERE pid=? and ln=? LIMIT 1");
		$success = $r->execute(array($pid, $ln));
		if ($success) if ($r->rowCount()) {
			$data = $r->fetch();
			if (isset($data['text'])) $this->Parse_html_output($data['text']);
			if (isset($data['info'])) $this->Parse_html_output($data['info']);
			if (isset($data['info2'])) $this->Parse_html_output($data['info2']);
			if (isset($data['info3'])) $this->Parse_html_output($data['info3']);
			return $data;
		}
	}
	public function Get_info_block($nr, $pid=0, $inherit=false, $fields=null) {
		$nr = (int)$nr;
		if ($nr < 1) return false;
		elseif ($nr == 1) $nr = '';
		if ($pid !== 'home' && $this->site->Is_loaded()) {
			if ($pid == 0) {
				$pid = $this->site->loaded_page['pid'];
				if (isset($this->site->loaded_page['info'.$nr])) {
					if (!empty($this->site->loaded_page['info'.$nr])) {
						return $this->site->loaded_page['info'.$nr];
					}
					elseif ($inherit) {
						$idp = $this->site->loaded_page['idp'];
						$r = $this->prepare("SELECT info$nr,idp FROM {$this->db_prefix}pages p JOIN {$this->db_prefix}content c ON c.pid=p.id and ln='{$this->site->ln}' WHERE p.id=?");
						while (1) {
							$s = $r->execute(array($idp));
							if (!$s) break;
							$d = $r->fetch();
							if (!empty($d['info'.$nr])) return $d['info'.$nr];
							elseif (!empty($d['idp'])) $idp = $d['idp'];
							else break;
						}
						return false;
					}
				}
				return false;
			}
		}
		if (!$pid) return false;
		if ($pid == 'home') {
			$r = $this->prepare("SELECT info$nr FROM {$this->db_prefix}pages p JOIN {$this->db_prefix}content c ON c.pid=p.id WHERE front=1 and ln=? LIMIT 1");
			$s = $r->execute(array($this->site->ln));
		}
		else {
			$r = $this->prepare("SELECT info$nr".(is_array($pid)?',p.id pid':'').(!empty($fields)?','.$fields:'')." FROM {$this->db_prefix}pages p JOIN {$this->db_prefix}content c ON c.pid=p.id and ln=? WHERE p.id".(is_array($pid)?' in('.implode(',', $pid).')':'=? LIMIT 1'));
			$params = array($this->site->ln);
			if (!is_array($pid)) $params[] = $pid;
			$s = $r->execute($params);
		}
		if ($s) if ($r->rowCount()) {
			if (is_array($pid)) {
				$data = array();
				while ($d = $r->fetch()) {
					if (isset($d['text'])) $this->Parse_html_output($d['text']);
					if (isset($d['info'])) $this->Parse_html_output($d['info']);
					if (isset($d['info2'])) $this->Parse_html_output($d['info2']);
					if (isset($d['info3'])) $this->Parse_html_output($d['info3']);
					$data[] = $d;
				}
			}
			else {
				$data = $r->fetchColumn();
				$this->Parse_html_output($data);
			}
			return $data;
		}
		return false;
	}
	//needs optimization
	public function Get_id_by_path($path) {
		if (empty($path)) return false;
		$path = $this->Parse_gallery_file_request($path);
		if (!$path['success']) {
			return false;
		}
		$path = $path['category_path'].'/'.$path['filename'];
		return (isset($this->gallery_request_map[$path])?$this->gallery_request_map[$path]:null);
	}
	public function Parse_gallery_file_request($request) {
		$request_items = explode('/', $request);
		$total_items = count($request_items);
		$filename = $request_items[$total_items-1];
		$thumbnail_type = '';
		if (preg_match("/^(thumbnail|small|large|(thumb-([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9])))$/", $request_items[$total_items-2], $matches)
		&& !preg_match("/^thumb-(thumbnail|small|large)$/", $request_items[$total_items-2])) {
			$thumbnail_type = isset($matches[3])?$matches[3]:$matches[0];
			if ($total_items > 2) {
				if ($total_items > 3) {
					$category_path = substr($request, 0, -(strlen($request_items[$total_items-2])+strlen($request_items[$total_items-1])+2));
				}
				else $category_path = $request_items[0];
			}
		}
		else {
			if ($total_items > 1) {
				if ($total_items > 2) {
					$category_path = substr($request, 0, -(strlen($request_items[$total_items-1])+1));
				}
				else $category_path = $request_items[0];
			}
		}
		return array(
			'success'=>true,
			'filename'=>$filename,
			'category_path'=>$category_path,
			'thumbnail_type'=>$thumbnail_type,
		);
	}
	//is it useful?
	public function Set($key, $value) {
		$this->data[$key] = $value;
	}
	public function Get_by_controller($ctrl) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}pages p"
		." WHERE p.site=? and p.deleted=0 and p.published=1 and (p.date_from is null or p.date_from<=?) and (p.date_to is null or p.date_to>=?)"
		." and controller=?");
		$now = time();
		$success = $r->execute(array($this->site->data['id'], $now, $now, $ctrl));
		$ids = array();
		if ($success) if ($r->rowCount()) {
			while ($id = $r->fetchColumn()) {
				$ids[] = $id;
			}
		}
		return $ids;
	}
	public function Get_subpages_list($pids, $where='') {
		if (!is_array($pids)) $pids = array((string)$pids);
		$ids = array();
		$sub_ids = $pids;
		while (count($sub_ids)) {
			$ids = array_merge($ids, $sub_ids);
			$sub = $this->Get_subpages($sub_ids, $where);
			$sub_ids = array();
			foreach ($sub as $d) $sub_ids[] = $d['pid'];
		}
		return $ids;
	}
	public function Get_trashed() {
		$r = $this->prepare("SELECT id pid FROM {$this->db_prefix}pages WHERE deleted=1 and idp=0");
		$s = $r->execute();
		if (!$s) return false;
		$pages = array();
		while ($d = $r->fetch()) {
			$pages[] = $d['pid'];
		}
		return $pages;
	}
	public function Get_unique_route() {
		
	}
}