<?php

declare(strict_types=1);

namespace Site\API;

class Application extends \Site\API\Base {
	public function Start($test_val, $def_val = 123) {
		return ['error' => false, 'test_val' => $test_val, 'def_val' => $def_val];
	}
}
