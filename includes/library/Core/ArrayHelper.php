<?php

declare(strict_types=1);

namespace Core;

class ArrayHelper {
	public static function IsAssoc(array $array): bool {
		return array_keys($array) !== range(0, count($array) - 1);
	}
}