<?php

namespace System\Web\CMS\Data
{
class LayoutArea extends \System\Data\ActiveRecord
{
	public static function FindByLayoutID($layout_id)
	{
		$App = \System\Web\Application::GetInstance();

		$db = \System\Data\Database::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;

		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_layout_areas',\System\Data\Database::Delim_Table).' WHERE '.$db->Delim('layout_id',\System\Data\Database::Delim_Column).' = :layout_id';
		$params = array('layout_id' => $layout_id);
		return $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\LayoutArea');
	}

	public function __construct($id=null)
	{
		$App = \System\Web\Application::GetInstance();

		parent::__construct(array(
			'table' => $App->Config->CMS->database->table_prefix.'_layout_areas',
			'primaryidname' => 'id',
			'columns' => array(
				'layout_id',
				'type_id',
				'name',
				'display_name'
			),
			'id' => $id
		));
	}
}
}