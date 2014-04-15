<?php

namespace System
{
class ArrayObject
{
	public static function IsAssoc($array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}
}
}