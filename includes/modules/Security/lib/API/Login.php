<?php

namespace Security\API {
	class Login extends \Site\API\Base {
		public function Post($username, $password) {
			$user = \Security\System::Login($username, $password);
			if( !$user )
				return ['error' => true, 'message' => 'Invalid Username/Password.'];
			
			if( $this->IsInGroup('glo') )
				return ['error' => false, 'redirect' => '/glo-agreement'];
			
			return ['error' => false, 'redirect' => '/'];
		}
		
		public function ForgotPassword($username) {
			$user = \Security\System::ForgotPassword($username);
			
			if( $user !== false ) {
				// Send Email
				$subject = 'Forgot Password Request';
				$data = [
					'guid' => $user->forgot_password_guid,
					'username' => $user->username
				];
				
				\Site\Systems\Email::SendTemplate($user->email, 'no-reply@texasbackinbusiness.com', $subject, 'admin-forgot-password', $data);
			}
			
			return ['error' => false, 'message' => 'If that email/username was associated with an account, an email will be sent with a link to reset your password.'];
		}
		
		public function ResetPassword($forgot_guid, $username, $new_password, $confirm_password) {
			if( \Security\System::CheckPassword($new_password) < 0.25 )
				return ['error' => true, 'message' => 'Your password must be atleast 6 characters. And should be much longer than that.'];
			
			if( $new_password != $confirm_password )
				return ['error' => true, 'message' => 'Your passwords do not match.'];
			
			$user = \Security\System::ResetPassword($forgot_guid, $username, $new_password);
			if( !isset($user) || $user === false )
				return ['error' => true, 'message' => 'Invalid reset request. You may have clicked an expired link. Please make sure you are using the latest reset email sent to you.'];
			
			\session_destroy();
			
			return ['error' => false, 'reset' => true];
		}
	}
}