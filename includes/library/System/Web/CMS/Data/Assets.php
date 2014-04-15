<?php

namespace System\Web\CMS\Data
{
class Asset extends \System\Data\ActiveRecord
{
	public function __construct($id=null)
	{
		$App = \System\Web\Application::GetInstance();

		parent::__construct(array(
			'table' => $App->Config->CMS->database->table_prefix.'_assets',
			'primaryidname' => 'id',
			'columns' => array(
				'name',
				'content_type',
				'size',
				'content'
			),
			'id' => $id
		));
	}
}
}