<?php declare(strict_types=1);

namespace TG\Modules\Security\Data;

class Group extends \Core\Data\ActiveRecord {
	public static function FindByName($name) {
		return static::FindOnlyBy(['name' => $name]);
	}
	
	protected static $table_name = 'security_groups';
	protected static $columns = [
		'id' => '+@!bigint',
		'name' => '*varchar[255]',
		'display_name' => 'varchar[500]',
		'desc' => '?text',
		'hidden' => 'bit'
	];
	
	protected static $relationships = [
		'User' => [
			'table' => 'security_user_groups',
			'local_id' => 'group_id',
			'foreign_id' => 'user_id',
			'class' => '\TG\Modules\Security\Data\User'
		],
		'Role' => [
			'table' => 'security_group_roles',
			'local_id' => 'group_id',
			'foreign_id' => 'role_id',
			'class' => '\TG\Modules\Security\Data\Role'
		],
	];
}
