<?php

namespace Security;

class System {
	protected static $hashCost = 10;
	protected static function getHashCost() {
		return static::$hashCost;
	}
	
	protected static $isDev = null;
	protected static $devGroup = null;
	
	protected static $roleNameCache = [];
	protected static $roleIdCache = [];
	protected static $userRolesCache = [];
	
	public static function GetDevGroup() {
		if( !isset(static::$devGroup) ) {
			static::$devGroup = \Security\Data\Group::FindByName('dev');
		}
		
		return static::$devGroup;
	}
	
	public static function IsDev() {
		if( !isset(static::$isDev) ) {
			$dg = static::GetDevGroup();
			$user = static::GetUser();
			
			if( isset($dg) && isset($user) && $user->HasGroup($dg->id) ) {
				static::$isDev = true;
			} else {
				static::$isDev = false;
			}
		}
		
		return static::$isDev;
	}
	
	public static function GetUser(): ?\Security\Data\User {
		return $_SESSION['User'] ?? null;
	}
	
	public static function IsLoggedIn() {
		return static::GetUser() != null;
	}
	
	public static function CreateUser($username, $password, $email, &$error) {
		if( static::UserExists($username, $email) ) {
			$error = 'Username or Email already exists in the database.';
			return null;
		}
		
		$user = new \Security\Data\User();
		$user->username = $username;
		$user->email = $email;
		$user->password = \password_hash($password, PASSWORD_DEFAULT, ["cost" => static::getHashCost()]);
		$user->forgot_password_guid = null;
		$user->last_login = null;
		$user->Save();
		
		return $user;
	}
	
	public static function GeneratePassword() {
		$ret = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
		$len = 16;
		
		for( $i=0; $i<$len; $i++ ) {
			$ret .= substr($chars, rand(0, strlen($chars)-1), 1);
		}
		
		return $ret;
	}
	
	public static function ChangePassword(\Security\Data\User $user, $curr_password, $new_password) {
		if( !isset($user) || !isset($user->id) || $user->id <= 0 )
			return false;
			
		if( !\password_verify($curr_password, $user->password) )
			return false;
			
		$user->password = \password_hash($new_password, PASSWORD_DEFAULT, ["cost" => static::getHashCost()]);
		$user->Save();
		
		return true;
	}
	
	public static function LoginAs(\Security\Data\User $user) {
		if( $user == null || !isset($user->id) || $user->id <= 0 )
			return false;
			
		$_SESSION['User'] = $user;
		static::$isDev = null;
		
		return true;
	}

	public static function Login($un, $pw) {
		$user = \Security\Data\User::FindByUsernameOrEmail($un, $un);
		
		if( !isset($user) || count($user) == 0 )
			return false;
		
		$user = $user[0];

		if( !isset($user) || !isset($user->id) || $user->id <= 0 || (strtolower($user->username) != strtolower($un) && strtolower($user->email) != strtolower($un)) )
			return false;
			
		if( !\password_verify($pw, $user->password) )
			return false;
		
		if( \password_needs_rehash($user->password, PASSWORD_DEFAULT, ["cost" => static::getHashCost()]) ) {
			$user->password = password_hash($pw, PASSWORD_DEFAULT, ["cost" => static::getHashCost()]);
		}
		
		$user->forgot_password_guid = null;
		$user->last_login = time();
		$user->Save();
		
		$_SESSION['User'] = $user;
		static::$isDev = null;
		
		return $user;
	}

	public static function Logout() {
		$_SESSION['User'] = null;
	}
	
	public static function UserExists($username, $email) {
		$users = \Security\Data\User::FindByUsernameOrEmail($username, $email);
		
		if( count($users) > 0 )
			return true;
			
		return false;
	}
	
	public static function ForgotPassword($un) {
		$users = \Security\Data\User::FindByUsernameOrEmail($un, $un);
		
		if( count($users) <= 0 )
			return false;
		
		$user = $users[0];
		
		if( $user == null || !isset($user->id) || $user->id <= 0 )
			return false;
			
		$user->forgot_password_guid = \Core\Guid::NewGuid();
		$user->Save();
		
		return $user;
	}
	
	public static function ResetPassword($guid, $un, $password) {
		$user = \Security\Data\User::FindByForgotGuid($guid);
		
		if( $user == null || !isset($user->id) || $user->id < 0 || (strtolower($user->username) != strtolower($un) && strtolower($user->email) != strtolower($un)) )
			return false;
		
		$user->password = \password_hash($password, PASSWORD_DEFAULT, ["cost" => static::getHashCost()]);
		$user->forgot_password_guid = null;
		$user->Save();
		
		return $user;
	}

	public static function GetRole($role) {
		$ret = null;
		
		if( $role instanceof \Security\Data\Role ) {
			$ret = $role;
		} else if( is_string($role) ) {
			if( isset(static::$roleNameCache[$role]) )
				return static::$roleNameCache[$role];

			$ret = \Security\Data\Role::FindByName($role);
		} else if( is_numeric($role) ) {
			if( isset(static::$roleIdCache[$role]) )
				return static::$roleIdCache[$role];
				
			$ret = \Security\Data\Role::Load($role);
		}

		static::$roleIdCache[$ret->id] = $ret;
		static::$roleNameCache[$ret->name] = $ret;
		
		return $ret;
	}

	public static function HasRole($role, $user = null) {
		if( !isset($user) && static::IsDev() )
			return true;
		
		if( !isset($user) )
			$user = static::GetUser();
		
		if( $user == null )
			return false;

		if( ($role = static::GetRole($role)) == null )
			return false;
			
		if( $user->HasRole($role->id) )
			return true;
			
		foreach( $user->GetGroups() as $g ) {
			if( $g->HasRole($role->id) )
				return true;
		}
		
		return false;
	}
	
	public static function Can($role) {
		if( static::IsDev() )
			return true;
		
		$user = static::GetUser();
		
		if( $user == null )
			return false;

		if( ($role = static::GetRole($role)) == null )
			return false;
		
		return static::HasRole($role);
	}

	private static $preloaded = false;
	public static function PreloadRoles() {
		if( static::$preloaded )
			return;

		$roles = \Security\Data\Role::FindAll();

		foreach( $roles as $role ) {
			static::$roleIdCache[$role->id] = $role;
			static::$roleNameCache[$role->name] = $role;
		}

		static::$preloaded = true;
	}

	public static function PreloadUserRoles() {
		static::PreloadRoles();

		$db = \Core\Data\Database::Get();
		$user = static::GetUser();

		$roles = $db->ExecuteQuery('CALL `sp_get_user_roles`(:user_id)', ['user_id' => $user->id], '\Security\Data\Role');

		$i = 0;
	}
	
	//TODO: Use to determine cost to use above
	protected static function getCostForHash() {
		$timeTarget = 0.5; // 500 milliseconds 

		$cost = 8;
		do {
			$cost++;
			$start = microtime(true);
			password_hash("test", PASSWORD_DEFAULT, ["cost" => $cost]);
			$end = microtime(true);
		} while (($end - $start) < $timeTarget);
		
		return $cost;
	}
	
	public static function CheckPassword($pw) {
		if( strlen($pw) < 8 )
			return 0;
		
		$score = 1;
		$strLen = count(array_unique(str_split($pw)));
		//$numGroups = count(array_unique($pw));
		
		$score *= $strLen / 8;
		
		$grps = 0;
		if( preg_match('/[a-z]+/', $pw) > 0 )
			$grps++;
		if( preg_match('/[A-Z]+/', $pw) > 0 )
			$grps++;
		if( preg_match('/[0-9]+/', $pw) > 0 )
			$grps++;
		if( preg_match('/[^a-zA-Z0-9]+/', $pw) > 0 )
			$grps++;
		
		$score *= $grps / 3;
		
		return $score;
	}
}