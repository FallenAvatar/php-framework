<?php

namespace Core\Handlers {
	class PageHandler extends \Core\Object implements IRequestHandler {
		protected $path;
		protected $rel_path;
		
		public function CanHandleRequest($App) {
			$this->path = $App->Request->Url->Path;
			$this->rel_path = $this->path;
			
			if( strpos($this->path, $App->Dirs->WebRoot) === false )
				$this->path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$this->path;

			$this->path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$this->path);
			if( !is_file($this->path) ) {
				if( is_dir($this->path) && is_file(\Core\IO\Path::Combine($this->path,'index.phtml')) ) {
					$this->path = \Core\IO\Path::Combine($this->path,'index.phtml');
					$this->rel_path = \Core\IO\Path::Combine($this->rel_path,'index.phtml');
				} else if( is_dir($this->path) && is_file(\Core\IO\Path::Combine($this->path,'index.php')) ) {
					$this->path = \Core\IO\Path::Combine($this->path,'index.php');
					$this->rel_path = \Core\IO\Path::Combine($this->rel_path,'index.php');
				} else if( is_file($this->path.'.phtml') ) {
					$this->path = $this->path.'.phtml';
					$this->rel_path = $this->rel_path.'.phtml';
				} else if( is_file($this->path.'.php') ) {
					$this->path = $this->path.'.php';
					$this->rel_path = $this->rel_path.'.php';
				} else
					return false;
			}
			
			$ext = substr($this->rel_path, strrpos($this->rel_path,'.')+1);
			$exts = array('php','phtml','html');

			if( !in_array($ext,$exts) )
				return false;

			return true;
		}

		public function ExecuteRequest($App) {
			$p = strrpos($this->rel_path,'.');
			$ext = substr($this->rel_path, $p+1);
			$this->rel_path = substr($this->rel_path, 0, $p);
			
			$parts = explode('/',$this->rel_path);
			$class_name = '\\Site\\Pages';
			
			$keywords = array('abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 
				'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 
				'endwhile', 'eval', 'exit', 'extends', 'final', 'finally', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 
				'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 
				'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 
				'while', 'xor', 'yield');

			foreach($parts as $part) {
				$pa = str_replace('-','_',strtolower(trim($part)));
				if( $pa == '' )
					continue;
				
				if( in_array($pa, $keywords) )
					$pa = '_'.$pa;

				$class_name .= '\\'.$pa;
			}

			$inst = null;

			if( strtolower($ext) == 'phtml' && is_file(substr($this->path, 0, strrpos($this->path, '.')).'.php') ) {
				require_once(substr($this->path, 0, strrpos($this->path, '.')).'.php');
				$inst = new $class_name();
			} else if( \Core\Autoload\StandardAutoloader::IsClassLoaded($class_name) || \Core\Autoload\StandardAutoloader::CanLoadClass($class_name) )
				$inst = new $class_name();
			else if( \Core\Autoload\StandardAutoloader::IsClassLoaded('\Site\Page') || \Core\Autoload\StandardAutoloader::CanLoadClass('\Site\Page') ) {
				$class_name = '\Site\Page';
				$inst = new \Site\Page();
			} else
				$inst = new \Core\Web\UI\Page();

			if( !($inst instanceof \Core\Web\UI\Page) )
				throw new \Exception('Page class ['.$class_name.'] found for url ['.$this->rel_path.'], but it does not extend \Core\Web\UI\Page.');

			$this->RunPage($inst, $this->path);
		}
		
		public function ExecuteErrorRequest($errorPagePath, $errorCode) {
			$class_name = '\\Site\\Pages\\Error\\_'.$errorCode;
			$inst = null;

			if( \Core\Autoload\StandardAutoloader::IsClassLoaded($class_name) || \Core\Autoload\StandardAutoloader::CanLoadClass($class_name) )
				$inst = new $class_name();
			else
				$inst = new \Core\Web\UI\Page();

			if( !($inst instanceof \Core\Web\UI\Page) )
				throw new \Exception('Page class ['.$class_name.'] found for url ['.$this->rel_path.'], but it does not extend \Core\Web\UI\Page.');

			$this->RunPage($inst, $errorPagePath);
		}

		protected function RunPage($page, $path) {
			$page->SetPath($path);
			
			$page->Init();
			$page->Load();
			$page->Render();
		}
	}
}
