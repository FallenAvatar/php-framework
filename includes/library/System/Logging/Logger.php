<?php

namespace System\Logging
{
	class Logger extends \System\Object
	{
		public static function Log($message, $level, $data = null)
		{
			$app = \System\Application::GetInstance();
			$config = $app->Config;
			
			if( $config->System->Logging->enabled != "true" )
				return true;
				
			foreach($config->System->Logging->handlers as $name => $value)
			{
				
			}
		}
	}
}