<?php

declare(strict_types=1);

namespace Core\Handlers;

class StaticFileHandler implements \Core\Handlers\IRequestHandler {
	protected static array $ct_map = [
			/* CSS */
			'css' => 'text/css',
			'scss' => 'text/css',

			/* JS */
			'js' => 'application/javascript',
			'jsp' => 'application/javascript',
			'json' => 'application/json',

			/* HTML */
			'htm' => 'application/html',
			'html' => 'application/html'
		];

	private string $abs_path;
	private string $ext;

	public function CanHandleRequest(\Core\Application $App): bool {
		$this->abs_path = $App->Request->Url->Path;
		$rel_path = $this->abs_path;

		if( strpos($this->abs_path, $App->Urls->WebRoot) === false )
			$this->abs_path = substr($App->Urls->WebRoot,0,strlen($App->Urls->WebRoot)-1).$this->abs_path;

		if( DS != '/' )
			$this->abs_path = str_replace('/', DS, $this->abs_path);

		$this->abs_path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot, $this->abs_path);
		$this->ext = substr($rel_path, strrpos($rel_path, '.')+1);

		if( !in_array($this->ext, array_keys(static::$ct_map)) )
			return false;

		if( is_file($this->abs_path) )
			return true;

		return false;
	}

	public function ExecuteRequest(\Core\Application $App, $data): void {
		header('Content-Type: '.static::$ct_map[$this->ext]);
		readfile($this->abs_path);
	}
}