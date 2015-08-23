<?php

namespace Core\Handlers {
	class StaticFileHandler implements \Core\Handlers\IRequestHandler {
		protected $url;
		protected $path;
		protected $ext;
		
		protected $mime_types = array(
			'css' => 'text/css',
			'xml' => 'text/xml',
			'json' => 'application/json',
			'html' => 'text/html'
		);
		
		public function CanHandleRequest($App) {
			$this->url = $App->Request->Url->Path;
			$this->ext = substr($this->url, strrpos($this->url,'.')+1);
			$this->path = str_replace('/', DS, $this->url);
			
			if( !in_array($this->ext, array_keys($this->mime_types)) )
				return false;
			
			if( strpos($this->path, $App->Dirs->WebRoot) === false )
				$this->path = substr($App->Dirs->WebRoot,0,strlen($this->Dirs->WebRoot)-1).$this->path;
			
			$this->path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$this->path);
			
			if( !is_file($this->path) )
				return false;
			
			return true;
		}

		public function ExecuteRequest($App) {
			$mt = 'application/octet-stream';
			if( isset($this->mime_types[$this->ext]) )
				$mt = $this->mime_types[$this->ext];
			header("Content-type: ".$mt);
			readfile($this->path);
		}
	}
}