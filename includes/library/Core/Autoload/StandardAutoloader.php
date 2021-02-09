<?php

declare(strict_types=1);

namespace Core\Autoload;

class StandardAutoloader {
	private static StandardAutoloader $_inst;
	public static function Get(): StandardAutoloader {
		if( !isset(static::$_inst) )
			static::$_inst = new static();

		return static::$_inst;
	}

	public static function Register($ns, $libPath) {
		static::Get()->RegisterNS($ns, $libPath);
	}

	private array $paths;
	public function __construct() {
		$this->paths = [];

		\spl_autoload_register([$this,'LoadClass'], true, true);
	}

	public function RegisterNS(string $ns, string $libPath): void {
		if( substr($ns, strlen($ns)-1) != '\\' )
			$ns .= '\\';

		if( $ns[0] == '\\' )
			$ns = substr($ns, 1);

		if( isset($this->paths[$ns]) )
			return;

		if( substr($libPath, strlen($libPath)-1) != DS )
			$libPath .= DS;

		$this->paths[$ns] = $libPath;
	}

	public function ClassExists(string $class_name): bool {
		return ($this->IsClassLoaded($class_name) || $this->CanLoadClass($class_name));
	}

	public function IsClassLoaded(string $class_name): bool {
		$classes = get_declared_classes();
		if( $class_name[0] == '\\' )
			$class_name = substr($class_name, 1);

		if( in_array($class_name, $classes) )
			return true;

		return false;
	}

	public function CanLoadClass(string $class_name): bool {
		if( $class_name[0] == '\\' )
			$class_name = substr($class_name, 1);

		$cn_path = $class_name;
		if( '\\' != DS )
			$cn_path = str_replace('\\', DS, $cn_path);

		foreach($this->paths as $ns => $libPath) {
			if( strlen($class_name) < strlen($ns) || substr($class_name, 0, strlen($ns)) != $ns )
				continue;

			$cn = substr($cn_path, strlen($ns));

			if( '\\' != DS )
				$cn = str_replace('\\', DS, $cn);

			$p = $libPath.$cn.".php";

			if( !is_file($p) )
				continue;

			return true;
		}

		return false;
	}

	public function LoadClass(string $class_name): void {
		if( $class_name[0] == '\\' )
			$class_name = substr($class_name, 1);

		$cn_path = $class_name;
		if( '\\' != DS )
			$cn_path = str_replace('\\', DS, $cn_path);

		foreach($this->paths as $ns => $libPath) {
			if( strlen($class_name) < strlen($ns) || substr($class_name, 0, strlen($ns)) != $ns )
				continue;

			$cn = substr($cn_path, strlen($ns));

			if( '\\' != DS )
				$cn = str_replace('\\', DS, $cn);

			$p = $libPath.$cn.".php";

			if( !is_file($p) )
				continue;

			require($p);
			break;
		}
	}
}