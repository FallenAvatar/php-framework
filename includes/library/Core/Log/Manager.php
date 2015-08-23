<?php

namespace Core\Log
{
	const LEVEL_DEBUG = 1;
	const LEVEL_INFO = 2;
	const LEVEL_WARN = 4;
	const LEVEL_ERROR = 8;
	
	class Manager
	{
		private static $logger = null;
		
		public static function Init()
		{
			if( self::$logger != null )
				return;

			$app = \Core\Application::GetInstance();
			$config = $app->Config->Core->Logging;
			
			if( !$config->enabled )
				return;
				
			$class_map = array(
				'db' => '\Core\Log\Storage\Database',
				'email' => '\Core\Log\Storage\Email',
				'file' => '\Core\Log\Storage\File'
			);
			
			$storages = array();
			foreach( $config->loggers as $name => $settings )
			{
				if( !$settings->enabled )
					continue;
				
				if( !isset($class_map[$name]) )
					continue;
				
				$class_name = $class_map[$name];
				$inst = new $class_name($settings);
				
				$storages[] = $inst;
			}
			
			self::$logger = new Logger($storages);
		}
		
		public static function Get()
		{
			if( self::$logger == null )
				self::Init();
				
			return self::$logger;
		}
	}
}