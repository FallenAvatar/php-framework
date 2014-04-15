<?php

namespace System\Web\CMS\Data
{
class AreaType extends \System\Data\ActiveRecord
{
	public function __construct($id=null)
	{
		$App = \System\Web\Application::GetInstance();

		parent::__construct(array(
			'table' => $App->Config->CMS->database->table_prefix.'_area_types',
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