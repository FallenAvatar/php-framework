<?

namespace Core\Handlers
{
	class PageHandler extends \Core\Object implements IRequestHandler
	{
		protected $path;
		protected $rel_path;
		
		public function CanHandleRequest($App)
		{
			$this->path = $App->Request->Url->Path;
			$this->rel_path = $this->path;
			
			if( strpos($this->path, $App->Dirs->WebRoot) === false )
				$this->path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$this->path;

			$this->path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$this->path);
			if( !is_file($this->path) )
			{
				if( is_dir($this->path) && is_file(\Core\IO\Path::Combine($this->path,'index.php')) )
				{
					$this->path = \Core\IO\Path::Combine($this->path,'index.php');
					$this->rel_path = \Core\IO\Path::Combine($this->rel_path,'index.php');
				}
				else if( is_file($this->path.'.php') )
				{
					$this->path = $this->path.'.php';
					$this->rel_path = $this->rel_path.'.php';
				}
				else if( is_file($this->path.'.phtml') )
				{
					$this->path = $this->path.'.phtml';
					$this->rel_path = $this->rel_path.'.phtml';
				}
				else
				{
					return false;
				}
			}
			
			$ext = substr($this->rel_path, strrpos($this->rel_path,'.')+1);
			$exts = array('php','phtml','html');

			if( !in_array($ext,$exts) )
				return false;

			return true;
		}

		public function ExecuteRequest($App)
		{
			$this->rel_path = substr($this->rel_path, 0, strrpos($this->rel_path,'.'));
			$parts = explode('/',$this->rel_path);
			$class_name = '\\Site\\Pages';

			foreach($parts as $part)
			{
				if( trim($part) == '' )
					continue;

				$class_name .= '\\'.$part;
			}

			$inst = null;

			if( \Core\Autoload\StandardAutoloader::IsClassLoaded($class_name) || \Core\Autoload\StandardAutoloader::CanLoadClass($class_name) )
				$inst = new $class_name();
			else
				$inst = new \Core\Web\UI\Page();

			if( !($inst instanceof \Core\Web\UI\Page) )
				throw new \Exception('Page class ['.$class_name.'] found for url ['.$this->rel_path.'], but it does not entend \Core\Web\UI\Page.');

			$inst->SetPath($this->path);

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
