<?php

namespace System\Web\Caching
{
	class Cache extends \System\Object
	{
		public static function CacheArray($path, $arr)
		{
			$cache = new Cache($path);
			$cache->settings = $arr;
			$cache->Save();
		}

		public static function CacheLoad($path)
		{
			$cache = new Cache($path);
			return $cache->settings;
		}

		private $file_path;
		private $settings;
		public function __construct($path)
		{
			$app = \System\Web\Application::GetInstance();

			$cache_dir = \System\IO\Path::Combine($app->Dirs->Data,'cache');

			$path_parts = explode('\\',$path);
			foreach($path_parts as $part)
				$cache_dir = \System\IO\Path::Combine($cache_dir,$part);

			$this->file_path = \System\IO\Path::Combine($cache_dir,'cache.json');

			if( !file_exists($this->file_path) )
				$this->settings = array();
			else
				$this->settings = json_decode(file_get_contents($this->file_path));
		}

		public function Save()
		{
			file_put_contents($this->file_path, json_encode($this->settings));
		}

		public function __get($name)
		{
			return $this->settings->$name;
		}

		public function __set($name, $value)
		{
			$this->settings->$name = $value;
		}
	
		public function __isset($name)
		{
			return isset($this->settings->$name);
		}

		public function __unset($name)
		{
			unset($this->settings->$name);
		}
	}
}