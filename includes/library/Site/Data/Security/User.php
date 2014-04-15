<?php

namespace Site\Data\Security
{
	class User extends \System\Data\ActiveRecord
	{
		public static function FindAll()
		{
			$db = \System\Data\Database::GetInstance();
			$sql = "SELECT * FROM `security_users`";
			
			return $db->ExecuteQuery($sql, array(), '\Site\Data\Security\User');
		}

		public static function FindByUsername($un)
		{
			$db = \System\Data\Database::GetInstance();
			$sql = "SELECT * FROM `security_users` WHERE `username` = :un OR `email` = :email";
			
			$rows = $db->ExecuteQuery($sql, array('un' => $un, 'email' => $un), '\Site\Data\Security\User');
			
			if( count($rows) != 1 )
				return null;
			
			return $rows[0];
		}
		
		public static function FindByUsernameOrEmail($un, $email)
		{
			$db = \System\Data\Database::GetInstance();
			$sql = "SELECT * FROM `security_users` WHERE (`username` IS NOT NULL AND `username` = :un) OR (`email` IS NOT NULL AND `email` = :email)";
			
			$rows = $db->ExecuteQuery($sql, array('un' => $un, 'email' => $email), '\Site\Data\Security\User');
			
			return $rows;
		}
		
		public static function FindByForgotGuid($guid)
		{
			$db = \System\Data\Database::GetInstance();
			$sql = "SELECT * FROM `security_users` WHERE `forgot_password_guid` = :guid";
			
			$rows = $db->ExecuteQuery($sql, array('guid' => $guid), '\Site\Data\Security\User');
			
			if( count($rows) != 1 )
				return null;
			
			return $rows[0];
		}
		
		public function __construct($id=null)
		{
			parent::__construct(array(
				'table' => 'security_users',
				'primaryidname' => 'id',
				'columns' => array(
					'username',
					'email',
					'password',
					'password_salt',
					'forgot_password_guid',
					'last_login'
				),
				'id' => $id
			));
		}
	}
}