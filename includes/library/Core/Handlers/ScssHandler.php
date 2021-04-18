<?php declare(strict_types=1);

namespace Core\Handlers;

class ScssHandler implements \Core\Handlers\IRequestHandler {
	public function CanHandleRequest(\Core\Application $App): bool {
		$path = $App->Request->Url->Path;
		$ext = substr($path, strrpos($path,'.')+1);

		if( $ext == 'css' )
			$path = substr($path, 0, strrpos($path,'.')) . '.scss';
		else if( $ext != 'scss' )
			return false;

		if( strpos($path, $App->Dirs->WebRoot) === false )
			$path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$path;

		$path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$path);

		if( !is_file($path) )
			return false;

		return true;
	}

	public function ExecuteRequest(\Core\Application $App, $data): void {
		$path = $App->Request->Url->Path;
		$ext = substr($path, strrpos($path,'.')+1);
		$rel_path = '';

		if( $ext == 'css' ) {
			$rel_path = $path;
			$path = substr($path, 0, strrpos($path,'.')) . '.scss';
		} else
			$rel_path = substr($path, 0, strrpos($path,'.')) . '.css';

		if( DS != '/' )
			$rel_path = str_replace('/', DS, $rel_path);

		if( strpos($path, $App->Dirs->WebRoot) === false )
			$path = substr($App->Dirs->WebRoot,0,strlen($App->Dirs->WebRoot)-1).$path;

		$path = \Core\IO\Path::Combine($App->Dirs->DocumentRoot,$path);
		$cached_path = \Core\IO\Path::Combine($App->Dirs->Cache,'core','web','scss',$rel_path);

		header("Content-type: text/css");

		if( file_exists($cached_path) ) {
			$mtime = filemtime($cached_path);
			if( filemtime($path) <= $mtime ) {
				$icache = $cached_path.'.imports';
				if (is_readable($icache)) {
					$imports = unserialize(file_get_contents($icache));
					$pass = true;
					foreach ($imports as $import) {
						if (filemtime($import) > $mtime)
							$pass = false;
					}

					if( $pass ) {
						header('X-SCSS-Cache: true');
						readfile($cached_path);
						return;
					}
				}
			}
		}

		$scss = new \Leafo\ScssPhp\Compiler();
		$scss->setImportPaths(dirname($path));

		$start = microtime(true);
		$css = $scss->compile(file_get_contents($path), $path);
		$elapsed = round((microtime(true) - $start), 4);

		$v = \Leafo\ScssPhp\Compiler::$VERSION;
		$t = date("r");
		$css = "/* compiled by scssphp $v on $t (${elapsed}s) */\n\n" . $css;

		$tpath = substr($cached_path, 0, strrpos($cached_path, DS));
		if( !file_exists($tpath) )
			mkdir($tpath, 0777, true);

		file_put_contents($cached_path, $css);
		file_put_contents($cached_path.'.imports', serialize($scss->getParsedFiles()));
		echo $css;
	}
}