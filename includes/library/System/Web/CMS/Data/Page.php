<?php

namespace System\Web\CMS\Data
{
class Page extends \System\Data\ActiveRecord
{
	public static function GetPages()
	{
		$App = \System\Web\Application::GetInstance();

		$db = \System\Data\Database::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;

		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_pages',\System\Data\Database::Delim_Table);
		$params = array();
		return $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\Page');
	}

	public static function FindByPath($path)
	{
		$App = \System\Web\Application::GetInstance();

		$db = \System\Data\Database::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;

		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_pages',\System\Data\Database::Delim_Table).' WHERE '.$db->Delim('path',\System\Data\Database::Delim_Column).' = :path';
		$params = array('path' => $path);
		$rows = $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\Page');
		
		if( count($rows) != 1 )
			return null;
		
		return $rows[0];
	}
	
	public function __construct($id=null)
	{
		$App = \System\Web\Application::GetInstance();

		parent::__construct(array(
			'table' => $App->Config->CMS->database->table_prefix.'_pages',
			'primaryidname' => 'id',
			'columns' => array(
				'layout_id',
				'name',
				'path',
				'title'
			),
			'id' => $id
		));
	}

	public function GetContent()
	{
		$App = \System\Web\Application::GetInstance();

		$db = \System\Data\Database::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;

		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_page_contents',\System\Data\Database::Delim_Table).' WHERE '.$db->Delim('page_id',\System\Data\Database::Delim_Column).' = :id';
		$params = array('id' => $this->id);
		return $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\PageContent');
	}
}
}