<?php

namespace System\IO
{
	class File extends \System\Object
	{
		public static function Exists($file_path)
		{
			return file_exists($file_path);
		}
	}
}