<?php declare(strict_types=1);

namespace Site\API;

class Base extends \Core\API\Base {
	protected function HasRole($role) {
		return \TG\Modules\Security\System::HasRole($role);
	}

	protected function Can($role) {
		return \TG\Modules\Security\System::Can($role);
	}

	protected function IsLoggedIn() {
		return \TG\Modules\Security\System::IsLoggedIn();
	}

	protected function IsInGroup($g) {
		return $this->IsloggedIn() && \TG\Modules\Security\System::GetUser()->HasGroup(\Site\Data\TG\Modules\Security\Group::FindByName($g)->id);
	}
}