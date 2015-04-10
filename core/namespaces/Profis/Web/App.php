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

namespace Profis\Web;

/**
 * Class App
 *
 * @property \Profis\Web\Request $request
 * @property \Profis\CMS\Components\UrlManager $urlManager
 *
 * @method static App app()
 *
 * @package Profis\Web
 */
class App extends \Profis\App {
	/** @var \Profis\Web\Controller */
	public $controller = null;

	/** @var \Profis\Web\Action */
	public $action = null;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getDefaultConfig() {
		return array_replace_recursive(
			parent::getDefaultConfig(),
			array(
				'components' => array(
					'request' => array(
						'class' => '\\Profis\\Web\\Components\\Request',
					),
					'urlManager' => array(
						'class' => '\\Profis\\Web\\Components\\UrlManager',
					),
				),
			)
		);
	}

	public function run() {
		parent::run();

		$requestInfo = $this->urlManager->processRequest();
		if( !$requestInfo )
			$this->end(404);

		if( !is_array($requestInfo) || !isset($requestInfo['controller'], $requestInfo['action']) )
			$this->end(500);

		$class = $requestInfo['controller'];
		if( isset($this->config['controllerMap'][$class]) )
			$class = $this->config['controllerMap'][$class];
		if( !class_exists($class) )
			throw new ControllerNotFoundException();
		$this->controller = new $class();

		$method = 'action' . ucfirst($requestInfo['action']);
		if( !method_exists($this->controller, $method) )
			throw new ActionNotFoundException();

		$this->controller->action = $this->action = new Action($requestInfo['action'], array($this->controller, $method));

		$this->beforeAction();
		$this->controller->run();
		$this->afterAction();
	}

	protected function beforeAction() {
		ob_start();
	}

	protected function afterAction() {
		$output = ob_get_clean();
		echo $output;
	}

	public function setHttpResponseCode($responseCode) {
		if( headers_sent() )
			return false;

		$stateCode = intval($responseCode);
		if( !$responseCode )
			return false;

		switch($stateCode) {
			case 100: $message = 'Continue'; break;
			case 101: $message = 'Switching Protocols'; break;

			case 200: $message = 'OK'; break;
			case 201: $message = 'Created'; break;
			case 202: $message = 'Accepted'; break;
			case 203: $message = 'Non-Authoritative Information'; break;
			case 204: $message = 'No Content'; break;
			case 205: $message = 'Reset Content'; break;
			case 206: $message = 'Partial Content'; break;

			case 300: $message = 'Multiple Choices'; break;
			case 301: $message = 'Moved Permanently'; break;
			case 302: $message = 'Found'; break;
			case 303: $message = 'See Other'; break;
			case 304: $message = 'Not Modified'; break;
			case 305: $message = 'Use Proxy'; break;
			case 307: $message = 'Temporary Redirect'; break;

			case 400: $message = 'Bad Request'; break;
			case 401: $message = 'Unauthorized'; break;
			case 402: $message = 'Payment Required'; break;
			case 403: $message = 'Forbidden'; break;
			case 404: $message = 'File Not Found'; break;
			case 405: $message = 'Method Not Allowed'; break;
			case 406: $message = 'Not Acceptable'; break;
			case 407: $message = 'Proxy Authentication Required'; break;
			case 408: $message = 'Request Timeout'; break;
			case 409: $message = 'Conflict'; break;
			case 410: $message = 'Gone'; break;
			case 411: $message = 'Length Required'; break;
			case 412: $message = 'Precondition Failed'; break;
			case 413: $message = 'Request Entity Too Large'; break;
			case 414: $message = 'Request-URI Too Long'; break;
			case 415: $message = 'Unsupported Media Type'; break;
			case 416: $message = 'Requested Range Not Satisfiable'; break;
			case 417: $message = 'Expectation Failed'; break;

			case 500: $message = 'Internal Server Error'; break;
			case 501: $message = 'Not Implemented'; break;
			case 502: $message = 'Bad Gateway'; break;
			case 503: $message = 'Service Unavailable'; break;
			case 504: $message = 'Gateway Timeout'; break;
			case 505: $message = 'HTTP Version Not Supported'; break;
			default: $message = 'Unknown Error';
		}
		header('HTTP/1.1 ' . $stateCode . ' ' . $message, true, $stateCode);
		return true;
	}

	public function end($exitCode = 0) {
		if( $exitCode )
			$this->setHttpResponseCode($exitCode);
		parent::end($exitCode);
	}
}