<?php

namespace System\Web
{
	class PageHandler implements \System\Web\IRequestHandler
	{
		public function CanHandleRequest($App)
		{
			$path = $App->Request->Url->Path;

			$rel_path = $path;
			
			if( strpos($path, $App->Dirs->WebRoot) === false )
				$path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$path;

			$path = \System\IO\Path::Combine($App->Dirs->DocumentRoot,$path);
			if( !is_file($path) )
			{
				if( is_dir($path) && is_file(\System\IO\Path::Combine($path,'index.php')) )
				{
					$path = \System\IO\Path::Combine($path,'index.php');
					$rel_path = \System\IO\Path::Combine($rel_path,'index.php');
				}
				else if( is_file($path.'.php') )
				{
					$path = $path.'.php';
					$rel_path = $rel_path.'.php';
				}
				else if( is_file($path.'.phtml') )
				{
					$path = $path.'.phtml';
					$rel_path = $rel_path.'.phtml';
				}
				else
				{
					return false;
				}
			}
			
			$ext = substr($rel_path, strrpos($rel_path,'.')+1);
			$exts = array('php','phtml','html');

			if( !in_array($ext,$exts) )
				return false;
			

			return true;
		}

		public function ExecuteRequest($App)
		{
			$path = $App->Request->Url->Path;

			$rel_path = $path;
			
			if( strpos($path, $App->Dirs->WebRoot) === false )
				$path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$path;

			$path = \System\IO\Path::Combine($App->Dirs->DocumentRoot,$path);
			if( !is_file($path) )
			{
				if( is_file($path.'.php') )
				{
					$path = $path.'.php';
					$rel_path = $rel_path.'.php';
				}
				else if( is_file($path.'.phtml') )
				{
					$path = $path.'.phtml';
					$rel_path = $rel_path.'.phtml';
				}
				else
				{
					$path = \System\IO\Path::Combine($path,'index.php');
					$rel_path = \System\IO\Path::Combine($rel_path,'index.php');
				}
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

			if( \System\Autoloader::IsClassLoaded($class_name) || \System\Autoloader::CanLoadClass($class_name) )
				$inst = new $class_name();
			else
				$inst = new \System\Web\Page();

			if( !($inst instanceof \System\Web\Page) )
				throw new \Exception('Page class ['.$class_name.'] found for url ['.$rel_path.'], but it does not entend \System\Web\Page.');

			$inst->SetPath($path);

			$this->RunPage($inst);
		}

		protected function RunPage($page)
		{
			$page->Init();
			$page->Load();
			$page->Render();
		}
	}
}