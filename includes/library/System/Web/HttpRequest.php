<?php

namespace System\Web
{
	class HttpRequest extends \System\Object
	{
		public function _getAcceptTypes() { return explode(',', $_SERVER["HTTP_ACCEPT"]); }
		
		// _getBrowser :: HttpBrowserCapabilities :: http://msdn.microsoft.com/en-us/library/system.web.httpbrowsercapabilities.aspx
		
		public function _getContentType() { return $_SERVER['CONTENT_TYPE']; }
		public function _getCookies() { return $_COOKIE; }
		
		protected $files;
		public function _getFiles() { return $this->files; }
		
		public function _getForm() { return $_POST; }
		public function _getHeaders() { return apache_request_headers(); }
		public function _getHttpMethod() { return $_SERVER["REQUEST_METHOD"]; }
		public function _getQueryString() { return $_GET; }
		
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
			$this->url = new \System\Web\URI($scheme.'://'.$host.(($port == '80' || $port == '443') ? '' : ':'.$port).$path.((isset($query) && trim($query) != '') ? '?'.$query : ''));
			$this->urlReferer = new \System\Web\URI($_SERVER["HTTP_REFERER"]);
			
			$this->files = array();
			if( isset($_FILES) )
			{
				$i = 0;
				foreach($_FILES as $name => $file_info)
				{
					$file = new \System\Web\HttpPostedFile($file_info);
					$this->files[$i] = $file;
					$this->files[$name] = $file;
				}
			}
		}
	}
}