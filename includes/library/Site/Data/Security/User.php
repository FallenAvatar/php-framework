<?php

namespace Site\Data\Security
{
	class User extends \Core\Data\ActiveRecord
	{
		public static function FindByUsername($un)
		{
			return static::FindOnlyBy(array('username' => $un));
		}
		
		public static function FindByUsernameOrEmail($un, $email)
		{
			return static::FindOnlyBy(array('username' => $un, 'email' => $email), 'OR');
		}
		
		public static function FindByForgotGuid($guid)
		{
			return static::FindOnlyBy(array('forgot_password_guid' => $guid));
		}
		
		public static $table_name = 'security_users';
		public static $columns = array(
			'id' => '+@!bigint',
			'username' => '*varchar[255]',
			'email' => '*varchar[255]',
			'password' => 'varchar[127]',
			'password_salt' => 'varchar[32]',
			'forgot_password_guid' => '#?varchar[36]',
			'last_login' => '?bigint'
		);
		
		public static $relationships = array(
			'Group' => array(
				'table' => 'security_user_groups',
				'local_id' => 'user_id',
				'foreign_id' => 'group_id',
				'class' => '\Site\Data\Security\Group'
			),
			'Role' => array(
				'table' => 'security_user_roles',
				'local_id' => 'user_id',
				'foreign_id' => 'role_id',
				'class' => '\Site\Data\Security\Role'
			),
			/*'M2M' => array(
				'plural_name' => 'M2Ms',
				'table' => 'lookup_table',
				'local_id' => 'this_id',
				'foreign_id' => 'that_id',
				'class' => 'OtherClass'
			),
			'O2M' => array(
				'plural_name' => 'O2Ms',
				'foreign_id' => 'this_id',
				'class' => 'OtherClass'
			),
			'M2O' => array(
				'plural_name' => 'M2Os',
				'local_id' => 'that_id',
				'class' => 'OtherClass'
			),
			'O2O' => array(
				'rel_type' => 'o2o',
				'local_id' => 'that_id',
				'class' => 'OtherClass'
			),
			'O2O2' => array(
				'rel_type' => 'o2o',
				'foreign_id' => 'this_id',
				'class' => 'OtherClass'
			),*/
		);
	}
}
