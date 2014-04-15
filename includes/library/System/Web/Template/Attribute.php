<?php

namespace System\Web\Template
{
class Attribute
{
	public $Name;
	public $Value;
	
	public function __construct($name,$value=null)
	{
		$this->Name = $name;
		$this->Value = $value;
	}
	
	public function __toString()
	{
		return $this->Name . "=\"" . htmlentities($this->Value) . "\"";
	}
}
}