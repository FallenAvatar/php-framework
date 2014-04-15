<?php

namespace System
{
abstract class Application extends Object
{
	protected static $s_inst = null;
	
	public static function GetInstance()
	{
		return self::$s_inst;
	}
	
	public static function Run()
	{
		$type = $GLOBALS['type'];
		$class = '';
		if( \System\Autoloader::CanLoadClass("\\Site\\".$type."\\Application") )
			$class = "\\Site\\".$type."\\Application";
		else
			$class = "\\System\\".$type."\\Application";
		
		self::$s_inst = new $class();
		
		if( !(self::$s_inst instanceof \System\Application) )
			throw new \Exception('Application class ['.$class.'] found, but it does not entend \System\Application.');
		
		self::$s_inst->_init();
		self::$s_inst->_run();
		
		exit();
	}
	
	protected $Config = null;
	public function _getConfig() { return $this->Config; }

	protected $Dirs;
	public function _getDirs() { return $this->Dirs; }
	
	protected function __construct()
	{
		$this->Dirs = new DynObject(array(), false, true);
		
		$this->BuildDirs();
	}
	
	protected function BuildDirs()
	{
		$this->AddDir('Root', $this->GetRootDir());
		$this->AddDir('Library', $this->Dirs->Root.'includes'.DS.'library'.DS);
		$this->AddDir('Configs', $this->Dirs->Root.'includes'.DS.'configs'.DS);
		$this->AddDir('Layouts', $this->Dirs->Root.'includes'.DS.'layouts'.DS);
		$this->AddDir('Data', $this->Dirs->Root.'includes'.DS.'data'.DS);
		$this->AddDir('Cache', $this->Dirs->Data.'cache'.DS);
	}
	
	protected function GetRootDir()
	{
		return realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS).DS;
	}
	
	protected function AddDir($name, $path)
	{
		$this->Dirs->$name = $path;
	}

	protected abstract function _init();
	protected abstract function _run();
}
}