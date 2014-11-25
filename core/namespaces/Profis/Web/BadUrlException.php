<?php
namespace Profis\Web;

class BadUrlException extends \Exception {
	private $url;

	public function __construct($url, $message = "Bad URL", $previous = null) {
		$this->url = $url;
		parent::__construct($message, 0, $previous);
	}

	public function getUrl() {
		return $this->url;
	}
}