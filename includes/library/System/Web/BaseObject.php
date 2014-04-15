<?php

namespace System\Web
{
class BaseObject extends \System\Object
{
	protected $Get;
	protected $Post;
	protected $Session;
	protected $Cookies;
	protected $Files;
	protected $Server;
	
	protected $OutHeaders;
	
	public function __construct()
	{
		$this->Get = $_GET;
		$this->Post = $_POST;
		$this->Session = $_SESSION;
		$this->Cookies = $_COOKIE;
		$this->Files = $_FILES;
		$this->Server = $_SERVER;
		
		$this->OutHeaders = array();
	}
	
	public function GetValue($name)
	{
		if( isset($this->Post[$name]) )
			return $this->Post[$name];
		
		return $this->Get[$name];
	}
	
	public function Redirect($url)
	{
		$this->Header('Location', $url, true);
		exit();
	}

	public function Header($name, $value, $overwrite = false)
	{
		if( !$overwrite && in_array($name, $this->OutHeaders) )
			return;
		
		$this->OutHeaders[] = $name;
		header($name.': '.$value);
	}
}
}