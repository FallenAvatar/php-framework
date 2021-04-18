<?php declare(strict_types=1);

namespace Core\Traits;

trait TStaticClass {
	use TSingleton;

	public static function __callStatic(string $name, $arguments) {
		$inst = static::Get();
		return call_user_func_array([$inst, $name], $arguments);
	}
}