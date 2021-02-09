<?php

declare(strict_types=1);

namespace Core;

class Exception extends \Exception {
	public static function ToJsonObject(\Throwable $ex): array {
		$inner = $ex->getPrevious();
		if( isset($inner) )
			$inner = static::ToJsonObject($inner);

		return [
			'message' => $ex->getMessage(),
			'code' => $ex->getCode(),
			'file' => $ex->getFile(),
			'line' => $ex->getLine(),
			'trace' => $ex->getTrace(),
			'inner' => $inner
		];
	}
}