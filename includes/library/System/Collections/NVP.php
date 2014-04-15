<?php

namespace System\Collection
{
class NVP
{
	protected $values;
	
	public function __construct($str)
	{
		$this->values = array();
		
		if( !isset($str) || $str == '' )
			return;
		
		$pairs = explode('&',$str);
		foreach( $pairs as $pair )
		{
			$parts = explode('=',$pair);
			if( count($parts) == 1 )
				$this->values[$parts[0]]='';
			else
			{
				$key = array_shift($parts);
				$this->values[$key] = implode("=", $parts);
			}
		}
	}
	
	public function __get($name)
	{
		return $this->values[$name];
	}
	
	public function __set($name,$value)
	{
		$this->values[$name]=$value;
	}
}
}