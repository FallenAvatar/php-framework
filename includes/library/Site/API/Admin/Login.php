<?php

namespace Site\API\Admin {
	class Login extends \Core\Web\BaseObject {
		public function Post($username, $password) {
			$user = \Site\Systems\Security::Login($username, $password);
			if( !$user )
				return array('error' => true, 'message' => 'Invalid Username/Password.');
			
			return array('error' => false, 'redirect' => '/admin/dashboard');
		}
		
		public function ForgotPassword($email) {
			
			if( ($user = \Site\Systems\Security::ResetPassword($email)) == false )
				return array('error' => true, 'message' => 'No user with that email found.');
			
			//TODO: Send email with forgot password link
			
			return array('error' => false, 'message' => 'An email has been sent with a link to reset your password.');
		}
	}
}