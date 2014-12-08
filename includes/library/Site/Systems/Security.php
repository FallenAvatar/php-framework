<?

namespace Site\Systems
{
	class Security
	{
		public static function GetUser()
		{
			return $_SESSION['User'];
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
			$user->password_salt = \System\Security\Cryptography::GenerateRandomSalt(22, \System\Security\Cryptography::Salt_Charset_Blowfish);
			$user->password = \System\Security\Cryptography\Blowfish::Hash($password, $user->password_salt);
			$user->forgot_password_guid = null;
			$user->last_login = null;
			$user->Save();
			
			return $user;
		}
		
		public static function ChangePassword(\Site\Data\Security\User $user, $curr_password, $new_password)
		{
			if( !isset($user) || !isset($user->id) || $user->id <= 0 )
				return false;
				
			$hash = \System\Security\Cryptography\Blowfish::Hash($curr_password, $user->password_salt);
			if( $user->password != $hash )
				return false;
				
			$user->password_salt = \System\Security\Cryptography::GenerateRandomSalt(22, \System\Security\Cryptography::Salt_Charset_Blowfish);
			$user->password = \System\Security\Cryptography\Blowfish::Hash($new_password, $user->password_salt);
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
			$user = \Site\Data\Security\User::FindByUsername($un);
			
			if( !isset($user) || !isset($user->id) || $user->id <= 0 || $user->username != $un )
				return false;
				
			$hash = \System\Security\Cryptography\Blowfish::Hash($pw, $user->password_salt);
			if( $user->password != $hash )
				return false;
			
			$_SESSION['User'] = $user;
			
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
		
		public static function ResetPassword(\Site\Data\Security\User &$user)
		{
			if( $user == null || !isset($user->id) || $user->id <= 0 )
				return false;
				
			$user->forgot_password_guid = \System\Guid::NewGuid();
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

		public static function HasRole($name, $ignore_dev = false;)
		{
			$user = static::GetUser();
			
			if( $user == null )
				return false;
				
			if( $ignore_dev == false && $user->HasGroup('dev') )
				return true;
			
			$role = \Site\Data\Security\Role::FindByName($name);
			
			if( $role == null )
				return false;
				
			if( $user->HasRole($role) )
				return true;
				
			foreach( $user->Groups as $g )
			{
				if( $g->HasRole($role) )
					return true;
			}
			
			return false;
		}
	}
}