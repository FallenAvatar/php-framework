<?php declare(strict_types=1);

namespace Core\Log;

const LEVEL_DEBUG = 1;
const LEVEL_INFO = 2;
const LEVEL_WARN = 4;
const LEVEL_ERROR = 8;

class Manager {
	private static ?Wrapper $wrapper = null;

	public static function Init(): void {
		self::$wrapper = new Wrapper();
		$config = \Core\Application::Get()->Config->Core->Logging;

		if( !$config->enabled )
			return;

		$class_map = [
			'db' => '\Core\Log\Storage\Database',
			'email' => '\Core\Log\Storage\Email',
			'file' => '\Core\Log\Storage\File'
		];

		foreach( $config->loggers as $name => $settings ) {
			if( !$settings->enabled )
				continue;

			if( !isset($class_map[$name]) )
				continue;

			$class_name = $class_map[$name];
			$inst = new $class_name($settings);

			self::$wrapper->AddLogger($inst);
		}
	}

	public static function GetLogger(): Wrapper {
		if( self::$wrapper == null )
			self::Init();

		return self::$wrapper;
	}
}