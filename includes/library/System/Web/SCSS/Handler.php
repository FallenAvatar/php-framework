<?php

namespace System\Web\SCSS
{
class Handler implements \System\Web\IRequestHandler
{
	protected $path;
	protected $orig_path;
	protected $rel_path;
	
	public function CanHandleRequest($App)
	{
		$this->orig_path = $this->path = $App->Request->Url->Path;
		$ext = substr($this->path, strrpos($this->path,'.')+1);
		
		if( $ext == 'css' )
			$this->path = substr($this->path, 0, strrpos($this->path,'.')) . '.scss';
		else if( $ext != 'scss' )
			return false;
		
		$this->rel_path = $this->orig_path;
		
		if( strpos($this->path, $App->Dirs->WebRoot) === false )
		{
			$this->path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$this->path;
			$this->orig_path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$this->orig_path;
		}
		
		$this->path = \System\IO\Path::Combine($App->Dirs->DocumentRoot,$this->path);
		$this->orig_path = \System\IO\Path::Combine($App->Dirs->DocumentRoot,$this->orig_path);
		
		if( !is_file($this->path) && !is_file($this->orig_path) )
		{
			if( $App->Config->System->debug == true )
				echo 'Path = ' . $this->path . "\n" . 'Original Path = ' . $this->orig_path . "\n";
			return false;
		}
		
		return true;
	}

	public function ExecuteRequest($App)
	{
		$cached_path = \System\IO\Path::Combine($App->Dirs->Cache,'system','web','scss',$this->rel_path);
		$lPath = $this->path;
		if( !is_file($lPath) )
			$lPath = $this->orig_path;
			
		header("Content-type: text/css");
		
		if( file_exists($cached_path) )
		{
			$mtime = filemtime($cached_path);
			
			if( !$App->Config->System->debug && filemtime($lPath) <= $mtime )
			{
				$icache = $cached_path.'.imports';
				if (is_readable($icache))
				{
					$imports = unserialize(file_get_contents($icache));
					$pass = true;
					foreach ($imports as $import)
						if (filemtime($import) > $mtime)
							$pass = false;
					
					if( $pass )
					{
						header('X-SCSS-Cache: true');
						readfile($cached_path);
						return;
					}
				}
			}
		}
		
		$scss = new \System\Web\SCSS\Compiler();
		$scss->setImportPaths(dirname($lPath));
		
		$start = microtime(true);
		$css = $scss->compile(file_get_contents($lPath), $lPath);
		$elapsed = round((microtime(true) - $start), 4);
		
		$v = \System\Web\SCSS\Compiler::$VERSION;
		$t = date("r");
		$css = "/* compiled by scssphp $v on $t (${elapsed}s) */\n\n" . $css;
		
		if( !is_dir(dirname($cached_path)) )
			mkdir(dirname($cached_path), 0777, true);
			
		file_put_contents($cached_path, $css);
		file_put_contents($cached_path.'.imports', serialize($scss->getParsedFiles()));
		echo $css;
	}
}
}