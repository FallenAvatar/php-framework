<?php

namespace Site
{
	class AdminPage extends \Core\Web\UI\Page
	{
		public function OnInit() {
			if( !\Site\Systems\Security::IsLoggedIn() ) {
				$this->Redirect('/admin/login');
				return;
		}
		}
		
		protected function RequireRole($role) {
			if( !is_numeric($role) ) {
				if( !($role instanceof \Site\Data\Security\Role) )
					throw new \Exception('Can not check an object of type ['.gettype($role).'] as a role.');
				
				$role = $role->id;
		}
		}
	}
}