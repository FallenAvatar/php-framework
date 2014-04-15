<?php

namespace System\Web\Template
{
class Node
{
	public $Name;
	public $NodeType;
	public $Attributes;
	
	public function __construct()
	{
		$this->Attributes=array();
	}
	
	public function GetAttribute($name)
	{
		return $this->Attributes[$name];
	}
	
	public function SetAttribute($name,$value)
	{
		$this->Attributes[$name]=$value;
	}
}
}