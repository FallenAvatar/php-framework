<?php

declare(strict_types=1);

namespace Site\API\Debug;

class Nav extends \Site\API\Base {
	public function Get() {
		$ret = [];

		$ret['item-1'] = [
			'loc' => 'item-1',
			'icon' => ''
		];

		$ret['item-2'] = [
			'loc' => 'item-2',
			'icon' => ''
		];
		
		return ['error' => false, 'menu' => $ret];
	}
}