<?php

namespace System\Web
{
	class Request
	{
		protected $uri;
		public function _getURI() { return $this->uri; }

		protected $post;
		public function _getPost() { return $this->post; }

		protected $get;
		public function _getGet() { return $this->get; }

		public function __construct()
		{
			$host = $_SERVER['HTTP_HOST'];
			$port = $_SERVER["SERVER_PORT"];
			$path = $_SERVER["REQUEST_URI"];
			$path_parts = explode('?',$path);
			$path = $path_parts[0];
			$query = $path_parts[1];
			$scheme = (($port == '443') ? 'https' : 'http');
			$url = $scheme.'://'.$host.(($port == '80' || $port == '443') ? '' : ':'.$port).$path.((isset($query) && trim($query) != '') ? '?'.$query : '');
			$this->uri = new \System\Web\URI($url);
			$this->post = $_POST;
			$this->get = $_GET;
		}

		public function __get($name)
		{
			$methodName = '_get'.$name;
			if( method_exists($this, $methodName) )
				return $this->$methodName();
			else
				throw new \Exception('Property ['.$name.'] not found.');
		}

		public function __set($name, $value)
		{
			$methodName = '_set'.$name;
			if( method_exists($this, $methodName) )
				$this->$methodName($value);
			else
				throw new \Exception('Property ['.$name.'] not found.');
		}
	}
}