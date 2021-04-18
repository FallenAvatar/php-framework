<?php declare(strict_types=1);

namespace TG\Modules\Security\Data;

class Role extends \Core\Data\ActiveRecord {
	public static function FindByName($name) {
		return static::FindOnlyBy(['name' => $name]);
	}
	
	public static $table_name = 'security_roles';
	public static $columns = [
		'id' => '+@!bigint',
		'name' => '*varchar[255]',
		'display_name' => 'varchar[500]'
	];
}
