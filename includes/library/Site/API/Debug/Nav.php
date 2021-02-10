<?php

declare(strict_types=1);

namespace Site\API\Debug;

class Nav extends \Site\API\Base {
	public function Get() {
		$ret = [];
		
		return ['error' => false, 'menu' => $ret];
	}
}