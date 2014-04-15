<?php

namespace System\Web\CMS\Data
{
class Layout extends \System\Data\ActiveRecord
{
	public static function FindByName($name)
	{
		$App = \System\Web\Application::GetInstance();

		$db = DB::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;

		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_layouts',DB::Delim_Table).' WHERE '.$db->Delim('name',DB::Delim_Column).' = :name';
		$params = array('name' => $name);
		$rows = $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\Layout');
		
		if( count($rows) != 1 )
			return null;
		
		return $rows[0];
	}

	public function __construct($id=null)
	{
		$App = \System\Web\Application::GetInstance();

		parent::__construct(array(
			'table' => $App->Config->CMS->database->table_prefix.'_layouts',
			'primaryidname' => 'id',
			'columns' => array(
				'name',
				'path'
			),
			'id' => $id
		));
	}

	public function GetAreas()
	{
		$App = \System\Web\Application::GetInstance();

		$db = \System\Data\Database::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;

		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_layout_areas',\System\Data\Database::Delim_Table).' WHERE '.$db->Delim('layout_id',\System\Data\Database::Delim_Column).' = :id';
		$params = array('id' => $this->id);
		return $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\LayoutArea');
	}
}
}