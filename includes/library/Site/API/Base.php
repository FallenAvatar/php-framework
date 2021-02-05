<?php

declare(strict_types=1);

namespace Site\API;

class Base extends \Core\API\Base {
	protected function HasRole($role) {
		return \Site\Systems\Security::HasRole($role);
	}

	protected function Can($role) {
		return \Site\Systems\Security::Can($role);
	}

	protected function IsLoggedIn() {
		return \Site\Systems\Security::IsLoggedIn();
	}

	protected function IsInGroup($g) {
		return $this->IsloggedIn() && \Site\Systems\Security::GetUser()->HasGroup(\Site\Data\Security\Group::FindByName($g)->id);
	}
}