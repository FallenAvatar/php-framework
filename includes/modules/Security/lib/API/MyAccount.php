<?php declare(strict_types=1);

namespace TG\Modules\Security\API;

class MyAccount extends \Site\API\Base {
	public function ChangePassword($curr_password, $new_password, $confirm_password) {
		if( !$this->Can('admin') )
			return ['error' => true, 'message' => 'You are not authorized.'];
		
		if( $new_password != $confirm_password )
			return ['error' => true, 'message' => 'New and Confirm passwords do not match.'];
		
		if( \TG\Modules\Security\System::CheckPassword($new_password) < 0.25 )
			return ['error' => true, 'message' => 'Your new password is not strong enough. It must be at least 8 characters long and contain many unique chaaracters. (Upper case, lower case, numbers, and symbols)'];
		
		if( $curr_password == $new_password )
			return ['error' => true, 'message' => 'Your new password must not be the same as your old password.'];
		
		$user = \TG\Modules\Security\System::GetUser();
		
		if( !$user )
			return ['error' => true, 'message' => 'You do not have permission to do that.'];
		
		$ret = \TG\Modules\Security\System::ChangePassword($user, $curr_password, $new_password);
		
		if( !$ret )
			return ['error' => true, 'message' => 'Your current password was incorrect.'];
		
		return ['error' => false, 'actions' => [['type' => 'redirect', 'url' => '#account']]];
	}
}
