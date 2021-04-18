<?php declare(strict_types=1);

namespace Site;

class Page extends \Core\Web\UI\Page {
	protected function RequireRole($role) {
		$r = \TG\Modules\Security\System::GetRole($role);

		if( !isset($r) )
			throw new \Exception('Argument $role is not a valid Role. Must be an integer, string or \TG\Modules\Security\Data\Role');

		return \TG\Modules\Security\System::HasRole($role);
	}
}