<?php

namespace Core\Handlers {
	class ScssHandler implements \Core\Handlers\IRequestHandler {
		protected $url;
		protected $url_wo_ext;
		protected $path;
		protected $ext;
		
		public function CanHandleRequest($App) {
			$this->url = $App->Request->Url->Path;
			$this->ext = substr($this->url, strrpos($this->url,'.')+1);
			$this->url_wo_ext = substr($this->url, 0, strrpos($this->url,'.'));

			if( !in_array($this->ext, array('css','scss')) )
				return false;
			
			$this->path = str_replace('/', DS, $this->url_wo_ext);
			
			if( strpos($this->path, $App->Dirs->WebRoot) === false )
				$this->path = substr($App->Dirs->WebRoot,0,strlen($this->Dirs->WebRoot)-1).$this->path;
			
			$this->path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$this->path.'.scss');
			
			if( !is_file($this->path) )
				return false;
			
			return true;
		}

		public function ExecuteRequest($App) {
			$cached_path = \Core\IO\Path::Combine($App->Dirs->Cache,'core','scss',str_replace('/',DS,$this->url_wo_ext));
			
			header("Content-type: text/css");
			
			if( file_exists($cached_path) )
			{
				$mtime = filemtime($cached_path);
				if( filemtime($this->path) <= $mtime )
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
			
			$scss = new \Leafo\ScssPhp\Compiler();
			$scss->setImportPaths(dirname($this->path));
			
			$start = microtime(true);
			$css = $scss->compile(file_get_contents($this->path), $this->path);
			$elapsed = round((microtime(true) - $start), 4);
			
			$v = \Leafo\ScssPhp\Version::VERSION;
			$t = date("r");
			$css = "/* compiled by scssphp $v on $t (${elapsed}s) */\n\n" . $css;
			
			$cp = substr($cached_path, 0, strrpos($cached_path, DS));
			if( !\is_dir($cp) )
				\mkdir($cp, 0777, true);
			
			file_put_contents($cached_path, $css);
			file_put_contents($cached_path.'.imports', serialize($scss->getParsedFiles()));
			echo $css;
		}
	}
}