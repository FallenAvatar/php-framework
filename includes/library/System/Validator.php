<?php

namespace System
{
	class Validator
	{
		public static function Factory($value,$type,$options=array)
		{
			$class = 'System_Validator_'.$type;
			return $class::Validate($type,$options);
		}
	}
}