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
			$menus = array(),
			$decoded_links = array();
	private $_menu_shift = 1,
			$_gmap_counter = 0,
			$_media_counter = 1;
	public function Init() {
		//constructor
	}
	
	/**
	 * Method for getting page id
	 * @return int
	 */
	public function get_id() {
		if (isset($this->page_data['pid'])) {
			return $this->page_data['pid'];
		}
		return false;
	}
	
	public function Get_route_data($route=null, $route_is_page_id=false, $path=array(), $internal_redirects=true) {
		$this->debug = true;
		$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'router/route.html', false, 25);
		$this->debug("Get_route_data($route)");
		$now = time();
		$r = $this->prepare("SELECT p.date,p.front,p.id pid,p.idp,c.*,p.controller,p.redirect,h.id redirect_from_home,p.nr,p.reference_id,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'routes.ln', 'routes.route'), array('separator'=>'▓'))." routes"
		.', ' . $this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'routes.ln', 'routes.permalink'), array('separator'=>'▓'))." permalinks"
		.(is_null($route)?
			" FROM {$this->db_prefix}pages p LEFT JOIN {$this->db_prefix}content c ON pid=p.id and c.ln=?"
			:" FROM {$this->db_prefix}content c JOIN {$this->db_prefix}pages p ON p.id=pid"
		)
		/*." FROM {$this->db_prefix}content c"
		." JOIN {$this->db_prefix}pages p ON p.id=pid"*/
		//check if home page redirects to this page
		." LEFT JOIN {$this->db_prefix}pages h ON h.front=1 and h.redirect=".$this->sql_parser->cast('p.id', 'text')
		." LEFT JOIN {$this->db_prefix}content routes ON routes.pid=p.id"
		." WHERE ".(is_null($route)?"p.front>0":($route_is_page_id?"p.id":"c.route")."=? ")
		." and p.site=? and p.deleted=0 and p.published=1 and (p.date_from is null or p.date_from<=?) and (p.date_to is null or p.date_to>=?)"
		.(!is_null($route)?" and c.ln=?":"")
		." GROUP BY ".$this->sql_parser->group_by('p.id,p.front,p.idp,p.controller,p.redirect,redirect_from_home,p.nr,c.id,c.pid,c.ln,c.name,c.info,c.info2,c.info3,c.title,c.keywords,c.description,c.route,c.text,c.last_update,c.update_by,p.date')
		." LIMIT 1");
		$params = array($this->site->data['id'], $now, $now);
		if (is_null($route)) array_unshift($params, $this->site->ln);
		else $params[] = $this->site->ln;
		if (!is_null($route)) array_unshift($params, $route);
		$success = $r->execute($params);
		if (!$success) {
			return array('controller'=>'core','data'=>'database_error');
		}
		if ($r->rowCount() != 1) {
			return array('controller'=>'core','data'=>404);
		}
		$this->page_data = $data = $r->fetch();
		if (empty($data['controller'])) {
			$this->debug('Controller is empty', 1);
			$this->debug($this->site->route, 2);
			if (count($this->site->route) > 2) {
				$this->debug("Redirecting to index, because we have extra routes", 5);
				$this->core->Redirect_local('', 301);
			}
		}
		$this->debug('page_data', 5);
		$this->debug($data, 6);
		$this->core->Parse_data_str($data['routes'], '▓', '░');
		$this->core->Parse_data_str($data['permalinks'], '▓', '░');
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
		
		if (!empty($data['permalink']) and strpos($_SERVER['REQUEST_URI'], $data['permalink'])=== false and !empty($data['route']) and $data['permalink'] != $data['route']) {
			$redirect_link = $data['permalink'] . $this->cfg['trailing_slash'];
			$this->debug("Redirecting to permalink {$data['permalink']}: $redirect_link", 5);
			$this->core->Redirect_local($redirect_link, 301);
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
			
			$route_data = $this->process_redirect($data, $internal_redirects);
			return $route_data;
		}
		if (empty($data['controller']) || $data['controller'] == 'menu') $data['controller'] = 'page';
		return $data;
	}
	
	/**
	 * 
	 * @param type $data
	 * @param type $internal_redirects
	 * @param type $append_range
	 * @return boolean or array
	 */
	public function process_redirect(&$data, $internal_redirects = true, $append_range = true) {
		v($data['redirect']);
		if (empty($data['redirect'])) {
			return false;
		}
		
		if (preg_match("#^http://#", $data['redirect'])) {
			$r_data = array(
				'controller'=> 'core',
				'action'=> 'http_redirect',
				'data'=> array(
					'url'=> $data['redirect'],
					'page'=> $data
				)
			);
			$this->core->Do_action($r_data['action'], $r_data['data']);
			return $r_data;
		}
		
		$controller_data = $this->get_controller_data_from_id($data['redirect']);
			
		$url = '';
		if ($controller_data and $this->core->Count_hooks('core/page/parse-page-url/'.$controller_data['plugin'])) {
			$this->core->Init_hooks('core/page/parse-page-url/'.$controller_data['plugin'], array(
				'url'=> &$url,
				'id' => $controller_data['id']
			));
			if (!empty($url)) {
				$this->core->Redirect_local($url, 301);
			}
		}
			
		$d = $this->Parse_redirect_data($data['redirect']);
		//shouldnt redirects be done internally?
		if (!$internal_redirects && !v($data['front'])) {
			//print_pre($d); exit();
			$url = $this->cfg['url']['base'];
			if (!empty($d['pid'])) {
				$redirect_page = $this->Get_page($d['pid']);
				$url .= $this->site->Get_link($redirect_page['route']);
				if ($append_range) {
					$url .= $this->routes->Get_range(2); //all except first
				}
				
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
	
	/**
	 * Returns array with keys <ul><li>plugin</li><li>id</li></ul>
	 * @param type $node_id
	 * @return array() | false
	 */
	public function get_controller_data_from_id($node_id) {
		if (preg_match("#^(".$this->cfg['patterns']['plugin_name'].")/(.+)$#i", $node_id, $m)) {
			//given id is with plugin prefix, what means that we need to generate tree items using that plugin tree renderer
			$plugin = $m[1];
			$node_id = $m[2];
			return array(
				'plugin' => $plugin,
				'id' => $node_id
			);
		}
		return false;
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
	public function Process_forms(&$text, $currentFormSubmitHash, $nextFormSubmitHash) {
		$dom = new DOMDocument();
		/* Create a fictional XHTML document with just the contents of $text in the body.
		 * Both DOCTYPE and character set definition are necessary for all the magic to work properly.
		 */
		@$dom->loadHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><head><meta http-equiv="Content-type" content="text/html; charset=utf-8" /></head><body>'.$text.'</body></html>');
		
		/*
		 * Generate informational array about all forms on the page
		 * and add honeypot fields to each form.
		 */
		$formElements = $dom->getElementsByTagName('form');

		if ($formElements->length) {
			$pageForms = array();
			for ($i=0; $i<$formElements->length; $i++) {
				$form = $formElements->item($i);
				$formId = $form->getAttribute('id');
				$formSettings = json_decode($form->getAttribute('pcformsettings'), true);
				$form->removeAttribute('pcformsettings');
				$formSubmitEmails = array();
				$thankYouText = '';
				if (!is_null($formSettings)) {
					if (array_key_exists('emails', $formSettings)) {
						$formSubmitEmails = explode(';', $formSettings['emails']);
					}
					if (array_key_exists('thankYouText', $formSettings)) {
						$thankYouText = $formSettings['thankYouText'];
					}
				}
				$formIdHash = 'pc_' . md5($formId.'_honeypot');
				$pageForm = array('status' => array('status' => 'initialized'), 'id' => $formId, 'idHash' => $formIdHash, 'submitEmails' => $formSubmitEmails, 'thankYouText' => $thankYouText, 'DOMElement' => &$form, 'fields' => array());
				
				foreach (array('input','textarea','select') as $tagName) {
					$inputs = $form->getElementsByTagName($tagName);
					for ($j=0; $j<$inputs->length; $j++) {
						$field = $inputs->item($j);
						$fieldName = preg_replace('/\[\]$/', '', $field->getAttribute('name'), -1, $multiple);
						if ($fieldName != '') {
							$type = ($tagName == 'input') ? $field->getAttribute('type') : $tagName;
							$multiple = $multiple || $field->hasAttribute('multiple') || ($type == 'checkbox');
							$nameAttribute = 'pc_' . md5($fieldName);
							$field->setAttribute('name', $nameAttribute . ($multiple?'[]':''));
							$pageForm['fields'][$fieldName]['multiple'] = $multiple;
							//$element = array();
							//$element['type'] = ($tagName == 'input' ? $field->getAttribute('type') : $tagName);
							//$element['required'] = $field->hasAttribute('required');
							//$element['DOMElement'] = &$field;
							//$pageForm['fields'][$fieldName]['elements'][] = $element;
							//if ($element['required']) {
								//$pageForm['fields'][$fieldName]['hasRequired'] = true;
							//}
							//if (!array_key_exists('hasRequired', $pageForm['fields'][$fieldName])) {
								//$pageForm['fields'][$fieldName]['hasRequired'] = false;
							//}
							$pageForm['fields'][$fieldName]['type'] = $type;
							$pageForm['fields'][$fieldName]['name'] = $nameAttribute;
							$pageForm['fields'][$fieldName]['required'] = $field->hasAttribute('required');
							if ($pageForm['fields'][$fieldName]['required']) {
								$field->setAttribute('data-msg-required', lang('form_field_required'));
							}
							$pageForm['fields'][$fieldName]['DOMElement'] = $field;
							if ($pageForm['fields'][$fieldName]['type'] == 'file') {
								$pageForm['fields'][$fieldName]['maxuploadsize'] = $field->getAttribute('data-maxuploadsize');
								if ($pageForm['fields'][$fieldName]['maxuploadsize']) {
									$field->setAttribute('data-msg-filetoobig', lang('form_file_too_big'));
								}
							}
							if ($tagName == 'select') {
								$fieldOptions = $field->getElementsByTagName('option');
								$options = array();
								for ($k=0; $k<$fieldOptions->length; $k++) {
									$fieldOption = $fieldOptions->item($k);
									$optionValue = $fieldOption->getAttribute('value');
									if ($optionValue == '') {
										// explicitly set value as empty
										$fieldOption->setAttribute('value', '');
									}
									$options[] = array('value' => $optionValue, 'title' => $fieldOption->nodeValue, 'DOMElement' => $fieldOption);
								}
								$pageForm['fields'][$fieldName]['options'] = $options;
							}
						}
					}
				}
				
				$honeyPotField = $form->appendChild(new DOMElement('input'));
				$honeyPotField->setAttribute('name', $formIdHash);
				$honeyPotField->setAttribute('type', 'hidden');
				$honeyPotField->setAttribute('value', $nextFormSubmitHash);
				
				$pageForms[] = $pageForm;
			}
			
			/*
			 * Process all forms in an array: check if each one has been
			 * submitted and validate the data if so.
			 */
			foreach ($pageForms as &$pageForm) {
				if (array_key_exists($pageForm['idHash'], $_POST) && ($_POST[$pageForm['idHash']] == $currentFormSubmitHash)) {
					$pageForm['status']['status'] = 'submitted';
					$values = array();
					$files = array();
					foreach ($pageForm['fields'] as $fieldName => &$field) {
						if ($field['type'] == 'file') {
							$error = false;
							$errmsg = false;
							if(array_key_exists($field['name'], $_FILES)) {
								$file = $_FILES[$field['name']];
								if(is_uploaded_file($file['tmp_name'])) {
									if(is_numeric($field['maxuploadsize']) && ($field['maxuploadsize'] != 0) && (filesize($file['tmp_name']) > $field['maxuploadsize'])) {
										$error = 'Uploaded file is too big.';
										$errmsg = lang('form_file_too_big');
									} else {
										$files[$fieldName] = $file;
									}
								} else {
									switch($file['error']) {
										case UPLOAD_ERR_NO_FILE:
											if ($field['required']) {
												$error = 'Required field missing.';
												$errmsg = lang('form_field_required');
											}
										break;
										case UPLOAD_ERR_INI_SIZE:
										case UPLOAD_ERR_FORM_SIZE:
											$error = 'Uploaded file is too big (PHP settings).';
											$errmsg = lang('form_file_too_big');
										break;
										default:
											$error = 'File upload error.';
											$errmsg = lang('form_file_upload_error');
										break;
									}
								}
							} elseif ($field['required']) {
								$error = 'Required field missing.';
								$errmsg = lang('form_field_required');
							}
							if ($error) {
								$field['DOMElement']->setAttribute('data-error', $errmsg);
								$pageForm['status'] = array('status' => 'error', 'errors' => array($error));
							}
						} else {
							if (array_key_exists($field['name'], $_POST)) {
								$values[$fieldName] = $_POST[$field['name']];
							}
							// relevant for checkboxes, radios, hidden fields and buttons only
							$defaultValue = $field['DOMElement']->getAttribute('value');
							$defaultValueSubmitted = array_key_exists($fieldName, $values) && (($defaultValue == $values[$fieldName]) || ($field['multiple'] && is_array($values[$fieldName]) && in_array($defaultValue, $values[$fieldName])));
							// relevant for freeform text inputs only
							$nonEmptyValueSubmitted = array_key_exists($fieldName, $values) && ((is_string($values[$fieldName]) && (trim($values[$fieldName]) != '')) || (is_array($values[$fieldName]) && !empty($values[$fieldName])));
							
							switch ($field['type']) {
								case 'checkbox':
								case 'radio':
									if ($defaultValueSubmitted) {
										$field['DOMElement']->setAttribute('checked', 'checked');
									} else {
										$field['DOMElement']->removeAttribute('checked');
										if ($field['required']) {
											$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
										}
									}
								break;
								case 'select':
									if (array_key_exists($fieldName, $values)) {
										$optionSelected = false;
										if ($field['multiple']) {
											if (is_array($values[$fieldName])) {
												foreach ($field['options'] as $option) {
													if (($option['value'] != '') && in_array($option['value'], $values[$fieldName])) {
														$option['DOMElement']->setAttribute('selected', 'selected');
														$optionSelected = true;
													}
												}
											}
										} else {
											if (!is_array($values[$fieldName])) {
												foreach($field['options'] as $option) {
													if (($option['value'] != '') && $option['value'] == $values[$fieldName]) {
														$option['DOMElement']->setAttribute('selected', 'selected');
														$optionSelected = true;
													}
												}
											}
										}
										if($field['required'] && !$optionSelected) {
											$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
										}
									} else {
										if ($field['required']) {
											$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
										}
									}
								break;
								case 'hidden':
								case 'reset':
								case 'submit':
								case 'button':
									// if required, check if this value exists in the submitted array
									if ($field['required'] && !$defaultValueSubmitted) {
										$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
									}
								break;
								case 'password':
									// same as above, but check for non-empty value
									if ($field['required'] && !$nonEmptyValueSubmitted) {
										$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
									}
								break;
								case 'textarea':
									// same as text fields (below) but sets nodeValue instead of a 'value' attribute
									if ($nonEmptyValueSubmitted) {
										if (is_array($values[$fieldName])) {
											$v = $values[$fieldName];
											$field['DOMElement']->nodeValue = array_shift($v);
										} else {
											$field['DOMElement']->nodeValue = $values[$fieldName];
										}
									} elseif ($field['required']) {
										$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
									}
								break;
								default:
									// for text fields, we simply check if a value has been submitted and
									// it is not an empty string or array. Not using empty() here, because
									// '0' is a valid result.
									if ($nonEmptyValueSubmitted) {
										if (is_array($values[$fieldName])) {
											$v = $values[$fieldName];
											$field['DOMElement']->setAttribute('value', array_shift($v));
										} else {
											$field['DOMElement']->setAttribute('value', $values[$fieldName]);
										}
									} elseif ($field['required']) {
										$pageForm['status'] = array('status' => 'error', 'errors' => array('Required field missing.'));
									}
								break;
							}
						}
					}
					if ($pageForm['status']['status'] == 'submitted') {
						if (!empty($files)) {
							$baseLocation = $this->cfg['path']['uploads'];
							foreach ($files as $inputName => $fileInfo) {
								$fileLocation = md5($pageForm['id']) . '/' . md5($inputName) . '/';
								$fullLocation = $baseLocation . $fileLocation;
								if(!is_dir($fullLocation) && !mkdir($fullLocation, 0777, true)) {
									$pageForm['status'] = array('status' => 'error', 'errors' => array('mkdir'));
									break;
								}
								$pathInfo = pathinfo($fileInfo['name']);
								$filePath = $fileLocation . $pathInfo['basename'];
								$fullPath = $baseLocation . $filePath;
								// ensure there is no file with this name yet
								for($i=0; file_exists($fullPath); $i++) {
									$filePath = $fileLocation . $pathInfo['filename'] . '_' . $i;
									if(array_key_exists('extension', $pathInfo)) {
										$filePath .= '.' . $pathInfo['extension'];
									}
									$fullPath = $baseLocation . $filePath;
								}
								if(move_uploaded_file($fileInfo['tmp_name'], $fullPath)) {
									$values[$inputName] = (object)array('name' => $fileInfo['name'], 'location' => $filePath, 'size' => $fileInfo['size']);
								} else {
									$pageForm['status'] = array('status' => 'error', 'errors' => array('move_uploaded_file'));
									break;
								}
							}
						}
						if ($pageForm['status']['status'] == 'submitted') {
							$r = $this->prepare("INSERT INTO {$this->db_prefix}forms (pid,form_id,data,time,ip) values(?,?,?,NOW(),?)");
							$s = $r->execute(array($this->page_data['pid'], $pageForm['id'], json_encode($values), ip2long($_SERVER['REMOTE_ADDR'])));
							if (!$s) {
								$pageForm['status'] = array('status' => 'error', 'errors'=>array('database'));
							} else {
								$pageForm['status'] = array('status'=> 'saved');
							}
						}
					}
					if ($pageForm['status']['status'] == 'saved') {
						if (!empty($pageForm['submitEmails'])) {
							$mail = new PHPMailer();
							$mail->SMTPDebug  = 1;
							$mail->CharSet = "utf-8";
							$mail->SetFrom(v($this->cfg['from_email']));
							$mail->Subject = lang('form_submitted_subject', $pageForm['id']);
														
							$mailBodyDOM = new DOMDocument;
							$mailBodyDOM->loadHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><head><meta http-equiv="Content-type" content="text/html; charset=utf-8" /></head><body></body></html>');
							$body = $mailBodyDOM->getElementsByTagName('body')->item(0);
							
							$body->appendChild(new DOMElement('p', lang('form_submitted_heading')));
							$body->appendChild(new DOMElement('p', lang('form_submitted_text', $pageForm['id'], $this->page_data['name'])));
							$textBody = lang('form_submitted_heading') . "\r\n\r\n" . lang('form_submitted_text', $pageForm['id'], $this->page_data['name']) . "\r\n\r\n";
							$table = $body->appendChild(new DOMElement('table'));
							foreach ($values as $fieldName => $value) {
								$row = $table->appendChild(new DOMElement('tr'));
								$headCell = $row->appendChild(new DOMElement('th', lang('form_submitted_field_name', $fieldName)));
								$textBody .= lang('form_submitted_field_name', $fieldName)."\r\n";
								switch(gettype($value)) {
									case 'array':
										$count = count($value);
										$headCell->setAttribute('rowspan', $count);
										$row->appendChild(new DOMElement('td', $value[0]));
										$textBody .= '    '.$value[0]."\r\n";
										for ($i=1; $i<$count; $i++) {
											$row = $table->appendChild (new DOMElement('tr'));
											$row->appendChild (new DOMElement('td', $value[$i]));
											$textBody .= '    '.$value[0]."\r\n";
										}
										$textBody .= "\r\n";
										break;
									case 'object':
										$row->appendChild (new DOMElement('td', lang('form_submitted_file', $value->name)));
										$textBody .= '    ' . lang('form_submitted_file', $value->name) . "\r\n\r\n";
										$mail->addAttachment($this->cfg['path']['uploads'].$value->location, $value->name);
										break;
									case 'string':
									default:
										$row->appendChild (new DOMElement('td', $value));
										$textBody .= '    '.$value."\r\n\r\n";
										break;
								}
							}
							$message = $mailBodyDOM->saveHTML();
							
							PC_utils::debugEmail($pageForm['submitEmails'], $message, $textBody);
							
							foreach ($pageForm['submitEmails'] as $submitEmail) {
								$mail->AddAddress($submitEmail);
							}
							
							$mail->Body = $message;
							$mail->AltBody = $textBody;
													
							if (!$mail->Send()) {
								$pageForm['status'] = array('status' => 'error', 'errors'=>array('email'));
								// echo 'Mailer error: ' . $mail->ErrorInfo;
							} else {
								$pageForm['status'] = array('status' => 'sent');
							}
						}
						
						if (!empty($pageForm['thankYouText'])) {
							// The following two lines allow to use HTML in the thank you text
							$thankYouDiv = $dom->createDocumentFragment();
							@$thankYouDiv->appendXML('<div class="pc_form_thank_you">' . $pageForm['thankYouText'] . '</div>');
							// The following two lines disallow use of HTML in the thank you text
							//$thankYouDiv = $dom->createElement('div', $pageForm['thankYouText']);
							//$thankYouDiv->setAttribute('class', 'pc_form_thank_you');
							$pageForm['DOMElement']->parentNode->replaceChild($thankYouDiv, $pageForm['DOMElement']);
						}
					}
				}
			}
			
			// replace current source code with the generated
			$body = $dom->getElementsByTagName('body')->item(0);
			$output = '';
			foreach ($body->childNodes as $child) {
				$output .= $dom->saveXML($child);
			}
			$text = $output;
			
			// hook up the form validation script
			$this->site->Add_script('media/jquery.PCFormValidation.js');
			
			// check if template for forms exists and include it if so
			$templateFile = $this->core->Get_theme_path(null, false).'template_forms.php';
			if (is_file($templateFile)) {
				$this->core->Output_start();
				include($templateFile);
				$text = $this->core->Output_end();
			}
		}
	}
	//single method that parses gallery file requests, replaces google maps objects, trims page break etc.
	public function Parse_html_output(&$t1, &$t2=null, &$t3=null, &$t4=null, &$t5=null) {
		// prevent double-submitting of forms. This goes here and not in Process_forms()
		// because we run Process_forms() multiple times for each page load.
		// This has a side-effect that if the user has multiple pages open in
		// different tabs, submitting both of them from the first time becomes impossible.
		$currentFormSubmitHash = null;
		if(array_key_exists('formSubmitHash', $_SESSION)) {
			$currentFormSubmitHash = $_SESSION['formSubmitHash'];
		}
		$nextFormSubmitHash = time();
		$_SESSION['formSubmitHash'] = $nextFormSubmitHash;
		
		//$this->page_data['pid']
		//echo $t1;
		$tc = 1;
		$var = 't'.$tc;
		$text =& $$var;
		while (isset($text)) {
			#
			$this->Replace_google_map_objects($text);
			$this->Parse_gallery_files_requests($text);
			$this->Process_forms($text, $currentFormSubmitHash, $nextFormSubmitHash);
			$this->core->Init_hooks('parse_html_output', array(
				'text'=> &$text
			));
			
			$this->Replace_media_objects($text);
			//fix hash links
			if (isset($this->route[1])) $text = preg_replace("/href=\"(#[^\"]+)\"/ui", "href=\"".$this->site->Get_link($this->route[1])."$1\"",  $text);
			//remove default language code from links
			
			$text = preg_replace("/href=\"".$this->site->default_ln."\//", "href=\"",  $text);
			$this->_decode_links($text);
			
			//append prefix to the links from editor
			//if (isset($this->route[1])) $text = preg_replace("/href=\"/ui", "href=\"".$this->site->link_prefix,  $text);
			if (isset($this->route[1])) $text = preg_replace("/href=\"(?!mailto:)/ui", "href=\"".$this->site->link_prefix,  $text);
						
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
	
	public function Get_encoded_page_link_by_id($id, $ln = '') {
		$encoded_link = 'pc_page:' . $id;
		if (!empty($ln)) {
			$encoded_link .= ':' . $ln;
		}
		$encoded_link .= '/';
		return $encoded_link;
	}
	
	protected function _replace_link_match($matches) {
		//print_pre($matches);
		$full_match = $matches[1];
		$full_match = rtrim($full_match,"/");
		$full_match_parts = explode('[', $full_match);
		$full_match = trim($full_match_parts[0]);
		$encoded_parts = explode(':', $full_match);
		$page_id_index = 0;
		if ($encoded_parts[0] == 'pc_page') {
			$page_id_index = 1;
		}
		$ln_index = $page_id_index + 1;
		$page_id = v($encoded_parts[$page_id_index]);
		$page_ln = v($encoded_parts[$ln_index]);
		
		$controller_data = $this->get_controller_data_from_id($page_id);
		$page_link = '';
		if ($controller_data and $this->core->Count_hooks('core/page/get-page-url/'.$controller_data['plugin'])) {
			$this->core->Init_hooks('core/page/get-page-url/'.$controller_data['plugin'], array(
				'url'=> &$page_link,
				'id' => $controller_data['id'],
				'ln' => $page_ln
			));
			if (!empty($page_link)) {
				//echo '<hr />' . $page_link;
				return $page_link;
			}
		}
		
		
		$page_link = $this->Get_page_link_by_id($page_id, $page_ln);
		if (v($matches[4]) == '/' and substr($page_link, -1) != '/') {
			$page_link .= '/';
		}
		//echo '<hr />' . $page_link;
		return $page_link;
	}
	
	protected function _decode_links(&$text) {
		//echo $text;
		//$text = preg_replace_callback("/(?<=href=\")[^\"]*((pc_page:)[^:]+(:\w+)?(\/?))/ui", 'PC_page::_replace_link_match', $text);
		$text =	preg_replace_callback("/(?<=href=\")[^\"]*((pc_page:)[^:]+(:\w+)?(\/?))/ui", array($this, '_replace_link_match'), $text);
				
		//$text = preg_replace("/(?<=href=\")[^\"]*((pc_page:)[^:]+(:\w+)?\/?)/ui", "http://www.nba.com/", $text);
		//echo $text;
	}
	
	public function Encode_emails($text) {
		$text = preg_replace_callback("#".$this->cfg['patterns']['email']."#i", array($this, 'Encode_email'), $text);
		return $text;
	}
	public function Encode_email($match) { //temp
		$link = (substr($match[0], 0, 7) == 'mailto:');
		return ($link?'mailto:':'').Hex_encode(($link?substr($match[0], 7):$match[0]), !$link);
	}
	public function Parse_gallery_files_requests(&$text) {
		if (!empty($text)) {
			//echo "<hr /><hr />Parse_gallery_files_requests()";
			//echo $text;
			//preg_match_all('#"((gallery/admin/id/(thumb-)?([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9]/)?)([0-9]+)")#i', $text, $matches);
			//preg_match_all('#(url\("?|")((gallery/admin/id/(thumb-)?([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9]/)?)([0-9]+))("?\)|")#i', $text, $matches);
			preg_match_all('#(url\("?|")(?:[^"]*)((gallery/admin/id/(thumb-)?([a-z0-9][a-z0-9\-_]{0,18}[a-z0-9]/)?)([0-9]+))("?\)|")#i', $text, $matches);
			//print_pre($matches);
			if (count($matches[6])) {
				//print_pre($matches);
				$query = "SELECT f.id,filename,"
					.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path
					FROM {$this->db_prefix}gallery_files f
					LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = category_id
					LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
					WHERE f.id in(".implode(',', $matches[6]).")
					GROUP BY f.id,f.filename";
				$r = $this->query($query);
				if ($r) {
					while ($data = $r->fetch()) {
						$this->gallery->Sort_path($data['path']);
						$data['path'] = !empty($data['path'])?$data['path'].'/':'';
						$files[$data['id']] = $data;
					}
					//print_pre($files);
					//print_pre($matches);
					for ($a=0; isset($matches[0][$a]); $a++) {
						$m_id = $matches[6][$a];
						$m_type = $matches[5][$a];
						$m_type_pre = $matches[4][$a];
						$m_full = $matches[2][$a];
						if (!isset($files[$m_id])) {
							continue;
						}
						$to = ''.$this->cfg['directories']['gallery'].'/'.$files[$m_id]['path'].$m_type_pre.$m_type.$files[$m_id]['filename'].'';
						$this->gallery_request_map[$files[$m_id]['path'].$files[$m_id]['filename']] = $m_id;
						//echo $to.'<br />';
						//echo '<hr /><hr />preg_replace(' . "#".$m_full."([^0-9])#" .',' . $to."$1";
						$text = preg_replace("#".$m_full."([^0-9])#", $to."$1", $text, -1, $count);
						if (!$count) {
							//echo '<hr />' . "str_replace($m_full, $to";
							$text = str_replace($m_full, $to, $text);
						}
					}
				}
			}
			//echo "<hr /><hr />parsed_html:";
			//echo $text;
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
				//print_pre($data);
				$map_custom_options = v($data->map_options);
				$map_custom_options = trim($map_custom_options);
				$map_custom_options = trim($map_custom_options, ',');
				if (!empty($map_custom_options)) {
					$map_custom_options = ', ' . $map_custom_options;
				}
				$marker_custom_options = v($data->marker_options);
				$marker_custom_options = trim($marker_custom_options);
				$marker_custom_options = trim($marker_custom_options, ',');
				if (!empty($marker_custom_options)) {
					$marker_custom_options = ', ' . $marker_custom_options;
				}
				$marker_image = v($data->marker_image);
				if (!empty($marker_image)) {
					
					//echo $marker_image = $this->Parse_gallery_file_request($marker_image);
					//print_pre($marker_image);
					$marker_image = ', icon:"' . $this->core->Absolute_url($marker_image) .'"';
				}
				
				$id = 'gmap_'.$this->_gmap_counter++;
				$w = $gmaps[3][$a];
				$h = $gmaps[4][$a];
				$google_map_frame = '<div id="'.$id.'" class="google-map" style="width:'.$w.'px;height:'.$h.'px;'.$gmaps[2][$a].'">'
				.'<script type="text/javascript">'
					.'var options_'.$id.'={zoom:'.$data->zoom.',center:new google.maps.LatLng('.$data->latitude.','.$data->longitude.'),mapTypeId:google.maps.MapTypeId.'.strtoupper($data->map_type).',streetViewControl:false'.(isset($data->scrollwheel)?',scrollwheel:'.$data->scrollwheel:''). $map_custom_options . '};'
					.'var '.$id.'=null;'
					.'$(function(){'
						.$id.'=new google.maps.Map(document.getElementById(\''.$id.'\'),options_'.$id.');'
						.'new google.maps.Marker({map:'.$id.',animation:google.maps.Animation.DROP,position: options_'.$id.'.center' . $marker_image . $marker_custom_options . '});'
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
					//jwplayer:
					$this->site->Add_script('media/jwplayer/jwplayer.js');
					$media_frame .= '<script type="text/javascript">$(document).ready(function() {
						jwplayer("'.$id.'").setup({'
							.'flashplayer:"media/jwplayer/player.swf",'
							.'file:"'.$this->cfg['url']['base'].$src.'",'
							.'height:'.$h.',width:'.$w
							.(!empty($poster)?",image:'".$base.$poster."'":"")
						.'});
					});
					</script>';
					//uppod:
					/*$media_frame .= "<script type=\"text/javascript\">var flashvars={m:'video',uid:'".$id."',file:'".$base.$src."'".(!empty($poster)?",poster:'".$base.$poster."'":"")
					.(!empty($skin)?',st:\''.$base.$skin."'":'')."};"
					."var params={id:'".$id."',wmode:'transparent',allowFullScreen:true,allowScriptAccess:'always'};"
					."new swfobject.embedSWF('".$base."media/uppod/uppod.swf', '".$id."', ".$w.", ".$h.", '9.0.115', false, flashvars, params);</script>";
					*/
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
			$cached =& $this->memstore->Get('page_pathes', $pid);
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
		return $this->memstore->Cache(array('page_pathes', $pid), $path);
	}
	
	public function Get_pages_data($select = '*', $where = '', $where_params = array(), $limit = '') {
		$where_s = $where;
		if (!empty($where_s)) {
			$where_s = ' WHERE ' . $where_s;
		}
		$limit_s = $limit;
		if (!empty($limit_s)) {
			$limit_s = ' LIMIT ' . $limit;
		}
		$query = "SELECT $select FROM {$this->db_prefix}pages $where_s $limit_s";
		$r = $this->prepare($query);
		$success = $r->execute($where_params);
		
		$single_value = true;
		if (strpos($select, ',') !== false or strpos($select, '*') === false) {
			$single_value = false;
		}
		$data = array();
		
		while ($d = $r->fetch()) {
			if (strpos($select, ',') !== false or strpos($select, '*') !== false) {
				$data[] = $d;
			}
			else {
				$data[] = $d[$select];
			}
		}
		return $data;
	}
	
	public function Get_page_data($page_id, $select = '*', $where = '', $limit = 1) {
		$where_s = $where;
		if (!empty($where_s)) {
			$where_s = ' AND ' . $where_s;
		}
		$limit_s = $limit;
		if (!empty($limit_s)) {
			$limit_s = ' LIMIT ' . $limit;
		}
		$query = "SELECT $select FROM {$this->db_prefix}pages WHERE id = ? $where_s $limit_s";
		$r = $this->prepare($query);
		$params = array();
		$params[] = $page_id;
		$success = $r->execute($params);
		
		if ($success) {
			if (strpos($select, ',') !== false or strpos($select, '*') !== false) {
				return $r->fetch();
			}
			else {
				return $r->fetchColumn();
			}
		}
		return false;
	}
	
	public function Get_page_parent_id($page_id) {
		$select = 'idp';
		return $this->Get_page_data($page_id, $select);
	}
	
	public function Get_page_site_id($page_id) {
		$select = 'site';
		return $this->Get_page_data($page_id, $select);
	}
	
	public function Get_id_by_content($name, $value, &$ln = null) {
		$queryParams = array();
		$queryParams[] = $value;
		$where_ln = '';
		if (!is_null($ln)) {
			$queryParams[] = $ln;
			$where_ln = ' AND ln = ?';
		}
		$query = "SELECT pid, ln FROM {$this->db_prefix}content WHERE $name = ? $where_ln LIMIT 1";
		$r_category = $this->prepare($query);
		
		$this->debug_query($query, $queryParams);
		
		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetch()) {
			$ln = $d['ln'];
			return $d['pid'];
		}
		return false;
	}
	
	public function Get_content_by_id($id, $name, $ln) {
		$query = "SELECT $name FROM {$this->db_prefix}content WHERE pid = ? AND ln = ? LIMIT 1";
		$r_category = $this->prepare($query);
		$queryParams = array();
		$queryParams[] = $id;
		$queryParams[] = $ln;

		$this->debug_query($query, $queryParams);
		
		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetchColumn()) {
			return $d;
		}
		return false;
	}
	
	public function Get_page($id=null, $parseLinks=true, $use_reference_id=false, $includeParents=false, $fields = array(), $lang = '') {
		if (is_array($id) and empty($id)) {
			return array();
		}
		$where = array();
		if (empty($lang)) {
			$lang = v($this->site->ln);
		}
		if (empty($lang)) {
			$lang = v($this->site->default_ln);
		}
		$params = array($lang);
		if (!is_null($id)) {
			if (!$use_reference_id) {
				if (is_array($id)) {
					$where[] = "p.id ".$this->sql_parser->in($id);
					$params = array_merge($params, $id);
				}
				else {
					$where[] = "p.id=?";
					$params[] = $id;
				}
				$where[] = "p.controller!='menu'";
			}
			else {
				$where[] = "reference_id=?";
				$params[] = $id;
			}
		}
		$fields_string = '*';
		if (!empty($fields)) {
			$fields_string = implode(',', $fields);
		}
		$query = "SELECT $fields_string"
			." FROM {$this->db_prefix}pages p"
			." LEFT JOIN {$this->db_prefix}content c ON c.pid=p.id and c.ln=?"
			.(count($where)?" WHERE ".implode(' and ', $where):"");

			
		$r = $this->prepare($query);
		$s = $r->execute($params);
		if (!$s) return false;
		if (!$r->rowCount()) return (is_null($id)?array():false);
		$list = array();
		$valid_html_fields = array('text', 'info', 'info2', 'info3');
		$needed_html_fields = array_intersect($valid_html_fields, $fields);
		while ($d = $r->fetch()) {
			if (!empty($fields)) {
				if (!empty($needed_html_fields)) {
					foreach ($valid_html_fields as $key => $value) {
						if (!isset($d[$value])) {
							$d[$value] = '';
						}
					}
				}
			}
			if ($parseLinks) $this->Parse_html_output($d['text'], $d['info'], $d['info2'], $d['info3']);
			$list[] = $d;
		}
		$valid_html_fields = array('text', 'info', 'info2', 'info3');

		
		if ($includeParents) {
			$idsList = array();
			foreach ($list as $p) $idsList[] = $p['idp'];
			$parentsList = $this->Get_page($idsList, $parseLinks);
			$parentsListKeyed = array();
			foreach ($parentsList as $pItem) $parentsListKeyed[$pItem['pid']] = $pItem;
			unset($parentsList);
			foreach ($list as &$p) $p['parent'] = $parentsListKeyed[$p['idp']];
		}
		return (is_null($id)||is_array($id)?$list:$list[0]);
	}
	
	/**
	 * Method for getting link to the page
	 * @param int $id
	 * @param string $ln can be array of get variables, 'ln' for language
	 * @return string|boolean
	 */
	public function Get_page_link_by_id($id, $ln = '') {
		$vars = array();
		if (is_array($ln)) {
			$vars = $ln;
			if (isset($ln['ln'])) {
				$ln = $ln['ln'];
				unset($vars['ln']);
			}
			else {
				$ln = '';
			}
		}
		$cache_key = $id . '/' . $ln . serialize($vars);
		if (isset($this->decoded_links[$cache_key])) {
			return $this->decoded_links[$cache_key];
		}
		$page_data = $this->Get_page($id, false, false, false, array('route', 'permalink'), $ln);
	
		$page_link = $this->Get_page_link_from_data($page_data, $ln);
		
		$vars_string = PC_utils::urlParamsToString($vars);
		if (!empty($vars_string)) {
			$page_link .= '?' . $vars_string; 
		}
		
		
		$this->decoded_links[$cache_key] = $page_link;
		
		return $page_link;
	}
	
	public function Get_page_link_from_data(&$page_data, $ln = '') {
		if (empty($ln)) {
			$ln = $this->site->ln;
		}
		if (isset($page_data['permalink']) and !empty($page_data['permalink'])) {
			$page_link =  $page_data['permalink'] . $this->cfg['trailing_slash'];
			return $page_link; 
		}
		$route = v($page_data['route']);
		if (empty($route)) {
			$route = v($page_data['pid']);
		}
		if (!empty($route)) {
			$page_link =  $route . $this->cfg['trailing_slash'];
			if (!empty($ln) and $ln != v($this->site->default_ln)) {
				if (strpos($page_link, $ln . '/') !== 0) {
					$page_link = $ln . '/' . $page_link;
				}
			}
			return $page_link;
		}
		return false;
	}
	
	public function Get_current_page_link($ln = '') {
		return $this->Get_page_link_by_id($this->get_id(), $ln);
	}
	
	public function Get_page_link_by_reference($reference, $ln = '') {
		$id = $this->Get_page_id_by_reference($reference);
		
		if ($id) {
			return $this->Get_page_link_by_id($id, $ln = '');
		}
	}
	
	public function Get_page_id_by_reference($reference) {
		$query_params = array($reference);
		$query = "SELECT id FROM {$this->db_prefix}pages
			WHERE reference_id = ? LIMIT 1";
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		
		$parts = array();
		
		if ($s ) {
			if($d = $r->fetch()) {
				return $d['id'];
			}
		}
		return false;
	}
	
	public function Get_page_anchors_by_id($id, $ln = '') {
		$anchors = array();
		$html_fields = array('text', 'info', 'info2', 'info3');
		$page_data = $this->Get_page($id, false, false, false, $html_fields, $ln);
		if (!is_array($page_data)) {
			return $anchors;
		}
		foreach ($page_data as $key => $value) {
			preg_match_all('/\<a\sname\s?=\s?"(.+)"/ui', $value, $matches);
			if (!empty($matches[1])) {
				$anchors = array_merge($anchors, $matches[1]);
			}
		}
		return $anchors;
	}
	
	public function Load_menu() {
		$now = time();
		$r = $this->prepare("SELECT mp.id idp,p.id pid,c.id cid,c.name,c.route,c.permalink,p.nr,p.hot,h.id redirect_from_home,p.controller,p.redirect,p.reference_id FROM {$this->db_prefix}pages mp"
		." LEFT JOIN {$this->db_prefix}pages p ON p.idp = mp.id"
		." AND p.controller!='menu' AND p.nomenu<1"
		." LEFT JOIN {$this->db_prefix}content c ON pid=p.id AND ln='{$this->site->ln}'"
		//check if home page rediects to this page
		." LEFT JOIN {$this->db_prefix}pages h ON h.front=1 and h.redirect=".$this->sql_parser->cast('p.id', 'text')
		." WHERE mp.controller='menu' and p.site=? and p.published=1 and p.deleted=0"
		." and (p.date_from is null or p.date_from<='$now') and (p.date_to is null or p.date_to>='$now')"
		." GROUP BY p.id"
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
			if (v($menu['permalink'])) {
				$menu['real_route'] = $menu['route'];
				$menu['route'] = $menu['permalink'];
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
					$html .= '<li'.$hot.'><a '.($this->site->Is_opened($item['pid'])?'style="font-weight:bold;" ':'').'href="'.$this->site->Get_link($item['route']).'">'.(!empty($item['name'])?$item['name']:'<i>#'.v($item['id']).'</i>').'</a></li>';
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
	public function Get_submenu($id, $fields=array(), &$limit=false, $include_content=true, $include_nomenu=false, $order = "mp.nr,p.nr") {
		if (!empty($fields) and !in_array('permalink', $fields)) {
			$fields[] = 'permalink';
		}
		//if (!empty($fields) and !in_array('pid', $fields)) {
		//	$fields[] = 'pid';
		//}
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
				'permalink'=> 'c.permalink',
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
				'nomenu'=> 'p.nomenu'
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
		
		$order_by = '';
		if (!empty($order)) {
			$order_by = ' ORDER BY ' . $order;
		}
		$limit_s = $limit;
		$paging = false;
		if (is_array($limit) and isset($limit['perPage'])) {
			$paging = true;
			$limit = new PC_paging(v($limit['page'], 1), v($limit['perPage'], 20), v($limit['start'], null));
			$limit_s = " {$limit->Get_offset()},{$limit->Get_limit()}";			
		}
		
		$query = "SELECT ".($paging?'SQL_CALC_FOUND_ROWS ':'').(!empty($retrieve_fields)?$retrieve_fields:"mp.id idp,p.id pid".($include_content?",c.id cid,c.name,c.route,c.permalink":'').",p.nr,p.hot,p.date").","
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
		." $order_by ".(($limit_s)?" limit $limit_s":"");
		

		$r = $this->prepare($query);
		$params = array();
		if (!is_array($id)) $params[] = $id;
		if (is_array($id) && !count($id)) return false;
		$success = $r->execute($params);
		if (!$success) return false;
		
		if ($paging) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $limit->Set_total($total = $rTotal->fetchColumn());
		}
		
		$items = array();
		if ($r->rowCount() >= 1) while ($menu = $r->fetch()) {
			if (isset($menu['pid'])) if ($menu['pid'] == v($this->site->loaded_page['pid'])) {
				$menu['current'] = true;
			}
			if (isset($menu['text'])) $this->Parse_html_output($menu['text']);
			if (isset($menu['info'])) $this->Parse_html_output($menu['info']);
			if (isset($menu['info2'])) $this->Parse_html_output($menu['info2']);
			if (isset($menu['info3'])) $this->Parse_html_output($menu['info3']);
			
			//print_pre($menu);
			
			if (v($menu['permalink'])) {
				$menu['real_route'] = $menu['route'];
				$menu['route'] = $menu['permalink'];
			}
			
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
	public function Get_by_controller($ctrl, $select = 'id') {
		$r = $this->prepare("SELECT $select FROM {$this->db_prefix}pages p"
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
	public function Get_by_reference($reference, $select = 'id') {
		$r = $this->prepare("SELECT $select FROM {$this->db_prefix}pages p"
		." WHERE p.site=? and p.deleted=0 and p.published=1 and (p.date_from is null or p.date_from<=?) and (p.date_to is null or p.date_to>=?)"
		." and reference_id=?");
		$now = time();
		$success = $r->execute(array($this->site->data['id'], $now, $now, $reference));
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
	public function Get_trashed($site_id = null) {
		$q_params = array();
		$cond = '';
		if (!is_null($site_id) and $site_id > 0) {
			$cond .= ' and site = ?';
			$q_params[] = $site_id;
		}
		$r = $this->prepare("SELECT id pid FROM {$this->db_prefix}pages WHERE deleted=1 and idp=0" . $cond);
		$s = $r->execute($q_params);
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