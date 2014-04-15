<?php

namespace System\Web\CMS\Data
{
class PageContent extends \System\Data\ActiveRecord
{

	public function __construct($id=null)
	{
		$App = \System\Web\Application::GetInstance();

		parent::__construct(array(
			'table' => $App->Config->CMS->database->table_prefix.'_page_contents',
			'primaryidname' => array('page_id','area_id'),
			'columns' => array(
				'content'
			),
			'id' => $id
		));
	}
}
}