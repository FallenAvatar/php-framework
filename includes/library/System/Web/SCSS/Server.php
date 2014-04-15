<?php

namespace System\Web\SCSS
{
	class Server {

		protected function join($left, $right)
		{
			return rtrim($left, "/") . "/" . ltrim($right, "/");
		}

		protected function inputName()
		{
			if (isset($_GET["p"]))
				return $_GET["p"];
			if (isset($_SERVER["PATH_INFO"]))
				return $_SERVER["PATH_INFO"];
			if (isset($_SERVER["DOCUMENT_URI"]))
				return substr($_SERVER["DOCUMENT_URI"], strlen($_SERVER["SCRIPT_NAME"]));
		}

		protected function findInput()
		{
			if ($input = $this->inputName())
			{
				$name = $this->join($this->dir, $input);
				if (is_readable($name))
					return $name;
			}
			
			return false;
		}

		protected function cacheName($fname)
		{
			return $this->join($this->cacheDir, md5($fname) . ".css");
		}

		protected function importsCacheName($out)
		{
			return $out . ".imports";
		}

		protected function needsCompile($in, $out)
		{
			if (!is_file($out))
				return true;

			$mtime = filemtime($out);
			if (filemtime($in) > $mtime)
				return true;

			// look for modified imports
			$icache = $this->importsCacheName($out);
			if (is_readable($icache))
			{
				$imports = unserialize(file_get_contents($icache));
				foreach ($imports as $import)
					if (filemtime($import) > $mtime)
						return true;
			}
			
			return false;
		}

		protected function compile($in, $out)
		{
			$start = microtime(true);
			$css = $this->scss->compile(file_get_contents($in), $in);
			$elapsed = round((microtime(true) - $start), 4);

			$v = \System\Web\SCSS\Compiler::$VERSION;
			$t = date("r");
			$css = "/* compiled by scssphp $v on $t (${elapsed}s) */\n\n" . $css;

			file_put_contents($out, $css);
			file_put_contents($this->importsCacheName($out), serialize($this->scss->getParsedFiles()));
			return $css;
		}

		public function serve()
		{
			if ($input = $this->findInput())
			{
				$output = $this->cacheName($input);
				header("Content-type: text/css");

				if ($this->needsCompile($input, $output))
				{
					try
					{
						echo $this->compile($input, $output);
					} catch (exception $e) {
						header('HTTP/1.1 500 Internal Server Error');
						echo "Parse error: " . $e->getMessage() . "\n";
					}
				} else {
					header('X-SCSS-Cache: true');
					echo file_get_contents($output);
				}

				return;
			}

			header('HTTP/1.0 404 Not Found');
			header("Content-type: text");
			$v = \System\Web\SCSS\Compiler::$VERSION;
			echo "/* INPUT NOT FOUND scss $v */\n";
		}

		public function __construct($dir, $cacheDir=null, $scss=null)
		{
			$this->dir = $dir;

			if (is_null($cacheDir))
				$cacheDir = $this->join($dir, "scss_cache");

			$this->cacheDir = $cacheDir;
			if (!is_dir($this->cacheDir))
				mkdir($this->cacheDir);

			if (is_null($scss)) {
				$scss = new \System\Web\SCSS\Compiler();
				$scss->setImportPaths($this->dir);
			}
			$this->scss = $scss;
		}

		static public function serveFrom($path)
		{
			$server = new self($path);
			$server->serve();
		}
	}
}