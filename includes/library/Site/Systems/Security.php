<?php

namespace Site\Systems
{
	class Security
	{
		public static function GetUser()
		{
			return $_SESSION['User'];
		}
		
		public static function IsLoggedIn()
		{
			return static::GetUser() != null;
		}
		
		public static function CreateUser($username, $password, $email, &$error)
		{
			if( static::UserExists($username, $email) )
			{
				$error = 'Username or Email already exists in the database.';
				return null;
			}
			
			$user = new \Site\Data\Security\User();
			$user->username = $username;
			$user->email = $email;
			$user->password_salt = \Core\Security\Cryptography::GenerateRandomSalt(22, \Core\Security\Cryptography::Salt_Charset_Blowfish);
			$user->password = \Core\Security\Cryptography\Blowfish::Hash($password, $user->password_salt);
			$user->forgot_password_guid = null;
			$user->last_login = null;
			$user->Save();
			
			return $user;
		}
		
		public static function UpdatePassword(\Site\Data\Security\User $user, $new_password)
		{
			if( !isset($user) || !isset($user->id) || $user->id <= 0 )
				return false;
				
			$user->password_salt = \Core\Security\Cryptography::GenerateRandomSalt(22, \Core\Security\Cryptography::Salt_Charset_Blowfish);
			$user->password = \Core\Security\Cryptography\Blowfish::Hash($new_password, $user->password_salt);
			$user->Save();
			
			return true;
		}
		
		public static function ChangePassword(\Site\Data\Security\User $user, $curr_password, $new_password)
		{
			if( !isset($user) || !isset($user->id) || $user->id <= 0 )
				return false;
				
			$hash = \Core\Security\Cryptography\Blowfish::Hash($curr_password, $user->password_salt);
			if( $user->password != $hash )
				return false;
				
			$user->password_salt = \Core\Security\Cryptography::GenerateRandomSalt(22, \Core\Security\Cryptography::Salt_Charset_Blowfish);
			$user->password = \Core\Security\Cryptography\Blowfish::Hash($new_password, $user->password_salt);
			$user->Save();
			
			return true;
		}
		
		public static function LoginAs(\Site\Data\Security\User $user)
		{
			if( $user == null || !isset($user->id) || $user->id <= 0 )
				return false;
				
			$_SESSION['User'] = $user;
			
			return true;
		}

		public static function Login($un, $pw)
		{
			$user = \Site\Data\Security\User::FindByUsernameOrEmail($un, $un);
			
			if( !isset($user) || !isset($user->id) || $user->id <= 0 || (strtolower($user->username) != strtolower($un) && strtolower($user->email) != strtolower($un)) )
				return false;
				
			$hash = \Core\Security\Cryptography\Blowfish::Hash($pw, $user->password_salt);
			if( $user->password != $hash )
				return false;
			
			$_SESSION['User'] = $user;
			
			$user->last_login = time();
			$user->Save();
			
			return $user;
		}

		public static function Logout()
		{
			$_SESSION['User'] = null;
		}
		
		public static function UserExists($username, $email)
		{
			$users = \Site\Data\Security\User::FindByUsernameOrEmail($username, $email);
			
			if( count($users) > 0 )
				return true;
				
			return false;
		}
		
		public static function ResetPassword($usernameOrEmail)
		{
			$users = \Site\Data\Security\User::FindByUsernameOrEmail($usernameOrEmail, $usernameOrEmail);
			
			if( count($users) <= 0 )
				return false;
			
			$user = $users[0];
			
			if( $user == null || !isset($user->id) || $user->id <= 0 )
				return false;
				
			$user->forgot_password_guid = \Core\Guid::NewGuid();
			$user->Save();
			
			return true;
		}
		
		public static function FindUser($guid)
		{
			$user = \Site\Data\Security\User::FindByForgotGuid($guid);
			
			if( $user == null || !isset($user->id) || $user->id < 0 )
				return null;
			
			return $user;
		}

		public static function HasRole($name)
		{
			$user = static::GetUser();
			
			if( $user == null )
				return false;

			$role = \Site\Data\Security\Role::FindByName($name);
			
			if( $role == null )
				return false;
				
			if( $user->HasRole($role->id) )
				return true;
				
			foreach( $user->GetGroups() as $g )
			{
				if( $g->HasRole($role->id) )
					return true;
			}
			
			return false;
		}
		
		public static function IsInGroup($g) {
			$user = static::GetUser();
			
			if( !isset($user) )
				return false;
			
			if( \is_string($g) )
				$g = \Site\Data\Security\Group::FindByName($g);
			else if( \is_numeric($g) )
				$g = new \Site\Data\Security\Group($g);
			
			return $user->HasGroup($g->id);
		}
		
		public static function GetRandomPassword($len = 8, $chars = null) {
			if( !isset($chars) )
				$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789()[]{};:,.<>/?!@#%&*';
			
			$max = strlen($chars) - 1;
			$ret = '';
			
			for( $i=0; $i<$len; $i++ )
				$ret .= $chars[rand(0,$max)];
		}
	}
}
