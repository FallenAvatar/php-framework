<?

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

			if( !$found )
				$app->ErrorPageHandler(404);
		}
	}
}
