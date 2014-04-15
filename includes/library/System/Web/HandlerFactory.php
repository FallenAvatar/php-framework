<?php

namespace System\Web
{
	class HandlerFactory extends \System\Object
	{
		public static function ProcessRequest()
		{
			$app = \System\Web\Application::GetInstance();
			$handler_classes = $app->Config->System->Web->handlers;

			$found = false;

			foreach($handler_classes as $class)
			{
				$handler = new $class();
				if( $handler->CanHandleRequest($app) )
				{
					$handler->ExecuteRequest($app);
					$found = true;
					break;
				}
			}

			if( !$found )
				$app->ErrorPageHandler(404);
		}
	}
}