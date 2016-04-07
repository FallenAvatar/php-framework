<?php

namespace Site\API\Admin\Forms\Security {
	class User extends \Core\Web\BaseObject {
		public function Edit($username, $email, $password = null, $confirm_password = null, $id = null) {
			if( isset($id) && trim($id) == '' )
				$id = null;
			
			if( isset($password) ) {
				if( $password != $confirm_password )
					return array('error' => true, 'message' => 'The passwords do not match.');
			}
			
			if( !isset($id) || trim($id) == '' ) {
				$err = null;
				$pw = null;
				if( !isset($password) )
					$pw = \Site\Systems\Security::GetRandomPassword();
				else
					$pw = $password;
				$user = \Site\Systems\Security::CreateUser($username, $pw, $email, $err);
				//TODO: Email password
				
				if( !isset($user) )
					return array('error' => true, 'message' => $err);
			} else {
				$id = intval($id);
				
				if( $id <= 0 )
					return array('error' => true, 'message' => 'Invalid User ID.');
				
				$user = new \Site\Data\Security\User($id);
			
				$user->username = $username;
				$user->email = $email;
				
				$user->Save();
				
				if( isset($password) ) {
					$user->password = $password;
					\Site\Systems\Security::UpdatePassword( $user, $password );
					//TODO: Email password
				}
			}
			
			return array('error' => false, 'message' => 'User '.(isset($id) ? 'updated' : 'created').' successfuly.', 'redirect' => '/admin/security/users/list');
		}
		
		public function ResetPassword($id) {
			$id = intval($id);
				
			if( id <= 0 )
				return array('error' => true, 'message' => 'Invalid User ID.');
			
			$user = new \Site\Data\Security\User($id);
			
			$user->password = \Site\Systems\Security::GetRandomPassword();
			
			$user->Save();
			
			//TODO: Email password
			
			return array('error' => false, 'message' => 'User\'s password has been reset.');
		}
	}
}