<?php declare(strict_types=1);

namespace Core\Caching;

class Cache extends \Core\Obj {
	public static function CacheArray(string $path, array $arr): void {
		$cache = new Cache($path);
		$cache->settings = $arr;
		$cache->Save();
	}

	public static function CacheLoad(string $path): array {
		$cache = new Cache($path);
		return $cache->settings;
	}

	private string $file_path;
	private array $settings;
	public function __construct(string $path) {
		$app = \Core\Application::Get();

		$cache_dir = \Core\IO\Path::Combine($app->Dirs->Data,'cache');

		$path_parts = explode('\\',$path);
		$fn = array_pop($path_parts);

		foreach($path_parts as $part)
			$cache_dir = \Core\IO\Path::Combine($cache_dir,$part);

		$this->file_path = \Core\IO\Path::Combine($cache_dir,$fn.'.json');

		if( !file_exists($this->file_path) )
			$this->settings = [];
		else
			$this->settings = json_decode(file_get_contents($this->file_path), true);
	}

	public function Save(): void {
		if( !is_dir(dirname($this->file_path)) )
			mkdir(dirname($this->file_path), 0777, true);

		file_put_contents($this->file_path, json_encode($this->settings));
	}

	public function __get(string $name) {
		return $this->settings[$name];
	}

	public function __set(string $name, $value): void {
		$this->settings[$name] = $value;
	}

	public function __isset(string $name): bool {
		return isset($this->settings[$name]);
	}

	public function __unset(string $name): void {
		unset($this->settings[$name]);
	}
}