<?php

namespace System\Web\CMS
{
class PageHandler extends \System\Web\PageHandler
{
	protected $cms_page;
	public function CanHandleRequest($App)
	{
		if( $App->Config->CMS->enabled != 'true' )
			return false;

		$path = $App->Request->URI->Path;
		
		if( strpos($path, $App->Dirs->WebRoot) === false )
			$path = substr($App->Dirs->WebRoot,0,strlen($this->Dirs->WebRoot)-1).$path;

		$db = \System\Data\Database::GetInstance($App->Config->CMS->database->connection);
		$prefix = $App->Config->CMS->database->table_prefix;
		
		$sql = 'SELECT * FROM '.$db->Delim($prefix.'_pages',\System\Data\Database::Delim_Table).' WHERE '.$db->Delim('path',\System\Data\Database::Delim_Column).' = :path';
		$params = array('path' => $path);
		$pages = $db->ExecuteQuery($sql,$params,'\System\Web\CMS\Data\Page');

		if( count($pages) == 0 )
			return false;

		$this->cms_page = $pages[0];

		return true;
	}

	public function ExecuteRequest($App)
	{
		$path = $App->Request->URI->Path;

		$rel_path = $path;
		
		if( strpos($path, $App->Dirs->WebRoot) === false )
			$path = substr($App->Dirs->WebRoot,0,strlen($this->Dirs->WebRoot)-1).$path;

		$path = \System\IO\Path::Combine($App->Dirs->DocumentRoot,$path);
		if( !is_file($path) )
		{
			$path = \System\IO\Path::Combine($path,'index.php');
			$rel_path = \System\IO\Path::Combine($rel_path,'index.php');
		}

		$rel_path = substr($rel_path, 0, strrpos($rel_path,'.'));
		$parts = explode('/',$rel_path);
		$class_name = '\\Site\\Pages';

		foreach($parts as $part)
		{
			if( trim($part) == '' )
				continue;

			$class_name .= '\\'.$part;
		}

		$inst = null;

		$app = \System\Web\Application::GetInstance();

		if( $app->IsClassLoaded($class_name) || $app->CanLoadClass($class_name) )
			$inst = new $class_name();
		else
			$inst = new \System\Web\CMS\Page();

		if( !($inst instanceof \System\Web\CMS\Page) )
			throw new \Exception('Page class ['.$class_name.'] found for url ['.$rel_path.'], but it does not entend \System\Web\Page.');

		$inst->SetDataPage($this->cms_page);

		$this->RunPage($inst);
	}
}
}