<?php declare(strict_types=1);

namespace Site;

class Page extends \Core\Web\UI\Page {
	protected function RequireRole($role) {
		$r = \Site\Systems\Security::GetRole($role);

		if( !isset($r) )
			throw new \Exception('Argument $role is not a valid Role. Must be an integer, string or \Site\Data\Security\Role');

		return \Site\Systems\Security::HasRole($role);
	}
}