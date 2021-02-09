<?php declare(strict_types=1);

namespace Core\Handlers;

class HandlerFactory extends \Core\Obj {
	public static function ProcessRequest(): string {
		$App = \Core\Application::Get();
		$handler_classes = $App->Config->Core->handlers;

		$found = false;
		$found_name = null;

		foreach($handler_classes as $name => $class) {
			$handler = new $class();
			if( ($data = $handler->CanHandleRequest($App)) !== false ) {
				$handler->ExecuteRequest($App, $data);
				$found = true;
				$found_name = $name;
				break;
			}
		}

		if( $found )
			return $found_name;

		static::ProcessErrorRequest(404);

		return 'error-404';
	}

	public static function ProcessErrorRequest(int $errorCode) {
		$txt = '';
		if( isset(\Core\Application::$HttpErrorCodeText[$errorCode]) )
			$txt = ' '.\Core\Application::$HttpErrorCodeText[$errorCode];

		header('HTTP/1.0 '.$errorCode.$txt);
		$App = \Core\Application::Get();
		$errorPath = $App->Dirs->Root.DS.'error'.DS.$errorCode.'.phtml';

		if( file_exists($errorPath) ) {
			// Print pretty error
			$handler = new \Core\Handlers\PageHandler();
			$handler->ExecuteErrorRequest($App, $errorPath, $errorCode);
		}
	}
}