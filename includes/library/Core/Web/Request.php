<?php

declare(strict_types=1);

namespace Core\Web;

class Request extends \Core\Obj {
	public function _getAcceptTypes(): array { return explode(',', $_SERVER["HTTP_ACCEPT"]); }
	public function _getContentType(): string { return $_SERVER['CONTENT_TYPE']; }

	public function _getHeaders(): array { return apache_request_headers(); }
	public function _getHttpMethod(): string { return $_SERVER["REQUEST_METHOD"]; }

	protected \Core\Web\URI $url;
	public function _getUrl(): \Core\Web\URI { return $this->url; }

	protected ?\Core\Web\URI $urlReferer;
	public function _getUrlReferer(): \Core\Web\URI { return $this->urlReferer; }

	public function _getUserAgent(): string { return $_SERVER["HTTP_USER_AGENT"]; }
	public function _getUserHostAddress(): string { return $_SERVER["REMOTE_ADDR"]; }
	public function _getUserLanguages(): array { return explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]); }

	public function __construct() {
		$host = $_SERVER['HTTP_HOST'];
		$port = $_SERVER["SERVER_PORT"];
		$path = $_SERVER["REQUEST_URI"];
		$path_parts = explode('?',$path);
		$path = $path_parts[0];
		$query = $path_parts[1];
		$scheme = (($port == '443') ? 'https' : 'http');
		$this->url = new \Core\Web\URI($scheme.'://'.$host.(($port == '80' || $port == '443') ? '' : ':'.$port).$path.((isset($query) && trim($query) != '') ? '?'.$query : ''));
		if( isset($_SERVER["HTTP_REFERER"]) )
			$this->urlReferer = new \Core\Web\URI($_SERVER["HTTP_REFERER"]);
	}
}