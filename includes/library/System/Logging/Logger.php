<?php

namespace System\Logging
{
	class Logger extends \System\Object
	{
	private static $handlers = null;
	
	private static function Init()
		{
			$app = \System\Application::GetInstance();
			$config = $app->Config;
			
			if( $config->System->Logging->enabled != "true" )
				return true;
				
			foreach($config->System->Logging->handlers as $name => $value)
			{
				
			}
		}
	
	public static function Log($message, $level, $data = null)
	{
		foreach(static::$handlers as $handler)
			$handler->Log($message, $level, $data);
	}
	}
}