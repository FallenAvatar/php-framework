<?

namespace Site\Data\Security
{
	class Role extends \Core\Data\ActiveRecord
	{
		public static function FindByName($name)
		{
			return static::FindOnlyBy(array('name' => $name));
		}
		
		public static $table_name = 'security_roles';
		public static $columns = array(
			'id' => '+@!bigint',
			'name' => '*varchar[255]',
			'display_name' => 'varchar[500]'
		);
	}
}