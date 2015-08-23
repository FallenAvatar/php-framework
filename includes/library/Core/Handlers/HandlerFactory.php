<?php

namespace Core\Handlers
{
	class HandlerFactory extends \Core\Object
	{
		public static function ProcessRequest()
		{
			$app = \Core\Application::GetInstance();
			$handler_classes = $app->Config->Core->handlers;

			$found = false;

			foreach($handler_classes as $name => $class)
			{
				$handler = new $class();
				if( $handler->CanHandleRequest($app) )
				{
					$handler->ExecuteRequest($app);
					$found = true;
					break;
				}
			}

			if( $found )
				return;
			
			static::ProcessErrorRequest(404);
		}
		
		public static function ProcessErrorRequest($errorCode)
		{
			$txt = '';
			if( isset(\Core\Application::$HttpErrorCodeText[$errorCode]) )
				$txt = ' '.\Core\Application::$HttpErrorCodeText[$errorCode];
			
			header('HTTP/1.0 '.$errorCode.$txt);
			$errorPath = \Core\Application::GetInstance()->Dirs->Root.DS.'error'.DS.$errorCode.'.phtml';
			
			if( file_exists($errorPath) )
			{
				// Print pretty error
				$handler = new \Core\Handlers\PageHandler();
				$handler->ExecuteErrorRequest($errorPath, $errorCode);
			}
		}
	}
}
