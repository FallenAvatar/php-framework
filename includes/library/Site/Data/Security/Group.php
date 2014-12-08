<?

namespace Site\Data\Security
{
	class Group extends \Core\Data\ActiveRecord
	{
		public static function FindAll($show_hidden = false)
		{
			$db = \Core\Data\Database::Get();
			$sql = "SELECT * FROM `security_groups`";
			
			if( !$show_hidden )
				$sql .= " WHERE `hidden` = 0";
			
			return $db->ExecuteQuery($sql, array(), '\Site\Data\Security\Group');
		}
		
		public static function FindByName($name)
		{
			$db = \Core\Data\Database::Get();
			$sql = "SELECT * FROM `security_groups` WHERE `name` = :name";
			
			$rows = $db->ExecuteQuery($sql, array('name' => $name), '\Site\Data\Security\Group');
			
			if( count($rows) <= 0 )
				return null;
			
			return $rows[0];
		}
		
		public function __construct($id=null)
		{
			parent::__construct(array(
				'table' => 'security_groups',
				'primaryidname' => 'id',
				'columns' => array(
					'name',
					'display_name',
					'hidden'
				),
				'id' => $id
			));
		}
	}
}