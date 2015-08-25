<?

namespace Site\Data\Security
{
	class Group extends \Core\Data\ActiveRecord
	{
		public static function FindByName($name)
		{
			return static::FindOnlyBy(array('name' => $name));
		}
		
		public static $table_name = 'security_groups';
		public static $columns = array(
			'id' => '+@!bigint',
			'name' => '*varchar[255]',
			'display_name' => 'varchar[500]',
			'hidden' => 'bit'
		);
		
		public static $relationships = array(
			'User' => array(
				'table' => 'security_user_groups',
				'local_id' => 'group_id',
				'foreign_id' => 'user_id',
				'class' => '\Site\Data\Security\User'
			),
			'Role' => array(
				'table' => 'security_group_roles',
				'local_id' => 'group_id',
				'foreign_id' => 'role_id',
				'class' => '\Site\Data\Security\Role'
			),
		);
	}
}