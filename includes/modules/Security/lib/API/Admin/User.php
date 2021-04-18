<?php declare(strict_types=1);

namespace Site\API\Security {
	class User extends \Site\API\Base {
		public function Create($username, $email, $groups = []) {
			if( !$this->Can('manage-security') )
				return array('error' => true, 'message' => 'You are not authorized.');
			
			$test = \Site\Data\TG\Modules\Security\User::FindByUsernameOrEmail($username, $email);
			
			if( isset($test) && count($test) > 0 )
				return array('error' => true, 'message' => 'That '.(($test[0]->username == $username) ? 'username' : 'email').' is associated with another user.');
			
			$pw = \TG\Modules\Security\System::GeneratePassword();
			$error = null;
			$user = \TG\Modules\Security\System::CreateUser($username, $pw, $email, $error);
			
			if( !isset($user) ) {
				return array('error' => true, 'message' => $error);
			}
			
			foreach( $groups as $group )
				$user->AddGroup($group);
			
			$subject = 'Your user account has been created';
			$body = '<html><head><title>'.$subject.'</title></head><body><p>A new user account has been created for you.</p><p><label>Username:</label> '.$username.'<br /><label>Password:</label> '.$pw.'</p><p><a href="http://bodybrite.com/admin/login">You can login here.</a></p></body></html>';
			
			$data = [
				'username' => $username,
				'password' => $pw
			];
			
			\Site\Systems\Email::SendTemplate($email, 'no-reply@na-eds.com', $subject, 'admin-account-registered', $data);

			return array('error' => false, 'message' => 'User has been created.', 'actions' => array(array('type' => 'redirect', 'url' => '#security/users')));
		}
		
		public function Edit($id, $username, $email, $groups = []) {
			if( !$this->Can('manage-security') )
				return array('error' => true, 'message' => 'You are not authorized.');
			
			if( !isset($id) || (!is_numeric($id) || ($id = intval($id)) <= 0) )
				return array('error' => true, 'message' => 'Invalid User ID.');
			
			$test = \Site\Data\TG\Modules\Security\User::FindByUsernameOrEmail($username, $email);
			
			if( isset($test) && count($test) > 0 && $test[0]->id != $id )
				return array('error' => true, 'message' => 'That '.(($test->username == $username) ? 'username' : 'email').' is associated with another user.');
			
			$item = new \Site\Data\TG\Modules\Security\User(intval($id));
			$item->username = $username;
			$item->email = $email;
			$item->Save();
			
			$item->ClearGroups();
			foreach( $groups as $group )
				$item->AddGroup($group);
			
			return array('error' => false, 'message' => 'User has been saved.', 'actions' => array(['type' => 'redirect', 'url' => '#security/users']));
		}
		
		public function Delete($id) {
			if( !$this->Can('manage-security') )
				return array('error' => true, 'message' => 'You are not authorized.');
			
			if( !isset($id) || (!is_numeric($id) || intval($id) <= 0) )
				return array('error' => true, 'message' => 'Invalid User ID.');
			
			$item = new \Site\Data\TG\Modules\Security\User(intval($id));
			$item->Delete();
			
			return array('error' => false, 'message' => 'User has been removed.', 'actions' => array(['type' => 'remove', 'ele' => '#users-table tr[data-id='.$id.']']));
		}
	}
}
