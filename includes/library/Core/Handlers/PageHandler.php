<?php

declare(strict_types=1);

namespace Core\Handlers;

class PageHandler extends \Core\Obj implements IRequestHandler {
	protected string $abs_path;
	protected string $rel_path;

	public function CanHandleRequest(\Core\Application $App): bool {
		$this->abs_path = $App->Request->Url->Path;
		$this->rel_path = $this->abs_path;

		if( strpos($this->abs_path, $App->Urls->WebRoot) === false )
			$this->abs_path = substr($App->Urls->WebRoot,0,strlen($App->Urls->WebRoot)-1).$this->abs_path;

		if( DS != '/' )
			$this->abs_path = str_replace('/', DS, $this->abs_path);

		$this->abs_path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$this->abs_path);
		if( !is_file($this->abs_path) ) {
			if( is_dir($this->abs_path) && is_file(\Core\IO\Path::Combine($this->abs_path,'index.phtml')) ) {
				$this->abs_path = \Core\IO\Path::Combine($this->abs_path,'index.phtml');
				$this->rel_path = \Core\IO\Path::Combine($this->rel_path,'index.phtml');
			} else if( is_dir($this->abs_path) && is_file(\Core\IO\Path::Combine($this->abs_path,'index.php')) ) {
				$this->abs_path = \Core\IO\Path::Combine($this->abs_path,'index.php');
				$this->rel_path = \Core\IO\Path::Combine($this->rel_path,'index.php');
			} else if( is_file($this->abs_path.'.phtml') ) {
				$this->abs_path = $this->abs_path.'.phtml';
				$this->rel_path = $this->rel_path.'.phtml';
			} else if( is_file($this->abs_path.'.php') ) {
				$this->abs_path = $this->abs_path.'.php';
				$this->rel_path = $this->rel_path.'.php';
			} else {
				return false;
			}
		}

		$ext = substr($this->rel_path, strrpos($this->rel_path,'.')+1);
		$exts = ['php','phtml','html'];

		if( !in_array($ext, $exts) )
			return false;

		return true;
	}

	public function ExecuteRequest(\Core\Application $App, $data): void {
		$this->rel_path = substr($this->rel_path, 0, strrpos($this->rel_path,'.'));
		$parts = explode('/',$this->rel_path);
		$class_name = '\\Site\\Pages';

		foreach($parts as $part) {
			if( trim($part) == '' )
				continue;

			$class_name .= '\\'.$part;
		}

		$inst = null;
		$al = \Core\Autoload\StandardAutoloader::Get();

		if( $al->IsClassLoaded($class_name) || $al->CanLoadClass($class_name) )
			$inst = new $class_name();
		else
			$inst = new \Core\Web\UI\Page();

		if( !($inst instanceof \Core\Web\UI\Page) )
			throw new \Exception('Page class ['.$class_name.'] found for url ['.$this->rel_path.'], but it does not extend \Core\Web\UI\Page.');

		header('Content-Type: text/html; charset=UTF-8');
		$this->RunPage($inst, $this->abs_path, $this->rel_path);
	}

	public function ExecuteErrorRequest(\Core\Application $App, string $errorPagePath, int $errorCode): void {
		$this->abs_path = $App->Request->Url->Path;
		$this->rel_path = $this->abs_path;

		$class_name = '\\Site\\Pages\\Error\\_'.$errorCode;
		$inst = null;
		$al = \Core\Autoload\StandardAutoloader::Get();

		if( $al->IsClassLoaded($class_name) || $al->CanLoadClass($class_name) )
			$inst = new $class_name();
		else
			$inst = new \Core\Web\UI\Page();

		if( !($inst instanceof \Core\Web\UI\Page) )
			throw new \Exception('Error Page class ['.$class_name.'] found for error code ['.$errorCode.'], but it does not extend \Core\Web\UI\Page.');

		$this->RunPage($inst, $errorPagePath, $this->rel_path);
	}

	protected function RunPage(\Core\Web\UI\Page $page, string $abs_path, string $web_path): void {
		$page->SetPath($abs_path, $web_path);

		$page->Init();
		$page->Load();
		$page->Render();
	}
}