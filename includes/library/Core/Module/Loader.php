<?php

declare(strict_types=1);

namespace Core\Module;

class Loader extends \Core\Obj {
	use \Core\Traits\TStaticClass;

	private function __construct() {
	}

	public function LoadAll(): void {
		$app = \Core\Application::Get();
		$module_dir = $app->dirs->Modules;

		$d = dir($module_dir);
		while( false !== ($entry = $d->read()) ) {
			if( $entry == '.' || $entry == '..' )
				continue;

			$mod_path = \Core\IO\Path::Combine($module_dir, $entry);

			if( !is_dir($mod_path) )
				continue;

			$mod_json = \Core\IO\Path::Combine($mod_path, 'module.json');

			if( !is_file($mod_json) ) {
				// TODO: Log
				continue;
			}

			$info = file_get_contents($mod_json);

			if( !isset($info) || $info === false || ($info = json_decode($info, true)) == null ) {
				trigger_error('Module has an invalid definition file.', E_WARNING);
				continue;
			}

			$app->RegisterModule($mod_path, $info);
		}
	}
}