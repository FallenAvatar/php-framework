<?php

namespace System
{
	class Autoloader
	{
		protected static $libPath;
	
		public static function Init($libPath)
		{
			self::$libPath = $libPath;
			spl_autoload_register('\System\Autoloader::LoadClass');
		}
	
		protected static function GetParts($class_name)
		{
			$parts = explode('\\',$class_name);
		
			$ret = array();
			
			for( $i=0; $i<count($parts)-1; $i++ )
				$ret[] = $parts[$i];
				
			$class_parts = explode('_', $parts[count($parts)-1]);
		
			foreach($class_parts as $part)
			{
				$ret[] = $part;
			}
		
			return $ret;
		}
	
		protected static function GetPathFromParts($parts)
		{
			return implode(DS, $parts).'.php';
		}
	
		protected static function GetPath($class_name)
		{
			return self::GetPathFromParts(self::GetParts($class_name));
		}
	
		public static function ClassExists($name)
		{
			return (self::IsClassLoaded($name) || self::CanLoadClass($name));
		}
	
		public static function IsClassLoaded($name)
		{
			$classes = get_declared_classes();

			if( $name[0] == '\\' )
				$name = substr($name,1);

			foreach($classes as $class)
				if( $class == $name )
					return true;

			return false;
		}

		public static function CanLoadClass($name)
		{
			$path = self::$libPath.self::GetPath($name);
		
			return (file_exists($path) && is_file($path));
		}
	
		public static function LoadClass($className)
		{
			$path = self::$libPath.self::GetPath($name);
		
			if( file_exists($path) && is_file($path) )
			{
				include_once($path);
				return;
			}
		
			$loaders = spl_autoload_functions();
		
			if( count($loaders) > 1 || (is_array($loaders[0]) && count($loaders[0]) > 1) )
				return false;
		
			throw new \Exception('Unable to load class ['.$className.'], expected path was ['.$path.'].');
		}
	}
}
