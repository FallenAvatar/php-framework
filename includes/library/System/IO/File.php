<?php

namespace System\IO
{
	class File extends \System\Object
	{
		public static function Exists($file_path)
		{
			return file_exists($file_path);
		}
		
	public static function ReadAllText($file_path)
	{
		if( !static::Exists($file_path) )
			throw new FileNotFoundException($file_path);
		
		return file_get_contents($file_path);
	}
	}
}