<?

namespace Core\Web
{
	class Request extends \Core\Object
	{
		public function _getAcceptTypes() { return explode(',', $_SERVER["HTTP_ACCEPT"]); }
		public function _getContentType() { return $_SERVER['CONTENT_TYPE']; }
		
		public function _getHeaders() { return apache_request_headers(); }
		public function _getHttpMethod() { return $_SERVER["REQUEST_METHOD"]; }
		
		protected $url;
		public function _getUrl() { return $this->url; }

		protected $urlReferer;
		public function _getUrlReferer() { return $this->urlReferer; }

		public function _getUserAgent() { return $_SERVER["HTTP_USER_AGENT"]; }
		public function _getUserHostAddress() { return $_SERVER["REMOTE_ADDR"]; }
		public function _getUserLanguages() { return explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]); }

		public function __construct()
		{
			$host = $_SERVER['HTTP_HOST'];
			$port = $_SERVER["SERVER_PORT"];
			$path = $_SERVER["REQUEST_URI"];
			$path_parts = explode('?',$path);
			$path = $path_parts[0];
			$query = $path_parts[1];
			$scheme = (($port == '443') ? 'https' : 'http');
			$this->url = new \Core\Web\URI($scheme.'://'.$host.(($port == '80' || $port == '443') ? '' : ':'.$port).$path.((isset($query) && trim($query) != '') ? '?'.$query : ''));
			$this->urlReferer = new \Core\Web\URI($_SERVER["HTTP_REFERER"]);
		}
	}
}