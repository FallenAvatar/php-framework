<?php

namespace Site\Data\Security
{
	class Role extends \System\Data\ActiveRecord
	{
		public static function FindAll()
		{
			$db = \System\Data\Database::GetInstance();
			$sql = "SELECT * FROM `security_roles`";

			return $db->ExecuteQuery($sql, array(), '\Site\Data\Security\Role');
		}
		
		public static function FindByName($name)
		{
			$db = \System\Data\Database::GetInstance();
			$sql = "SELECT * FROM `security_roles` WHERE `name` = :name";
			
			$rows = $db->ExecuteQuery($sql, array('name' => $name), '\Site\Data\Security\Role');
			
			if( count($rows) <= 0 )
				return null;
			
			return $rows[0];
		}
		
		public function __construct($id=null)
		{
			parent::__construct(array(
				'table' => 'security_roles',
				'primaryidname' => 'id',
				'columns' => array(
					'name',
					'display_name'
				),
				'id' => $id
			));
		}
	}
}