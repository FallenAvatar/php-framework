<?php

namespace System\Configuration
{
class Config extends \System\DynObject
{
	protected $errorStr;

	public function __construct($filename)
	{
		$this->errorStr = null;

		if( !\System\IO\File::Exists($filename) )
			throw new \Exception('Config file ['.$filename.'] not found.');

		set_error_handler(array($this, '_parseIniError'));
        $iniArray = parse_ini_file($filename, true);
        restore_error_handler();

		if( isset($this->errorStr) )
			throw new \Exception('Error parsing config file. ['.$filename.']: '.$this->errorStr);

		$config = $this->processSections($iniArray);

		parent::__construct($config, true);
	}

	protected function _parseIniError($errno, $errstr, $errfile, $errline)
    {
		$this->errorStr = $errstr;
    }

	protected function processSections($iniArray)
	{
		$ret = array();

		foreach($iniArray as $name => $iniSection)
		{
			$section = array();

			foreach($iniSection as $n => $v)
			{
				$n_parts = explode('.',$n);
				$curr = &$section;

				foreach($n_parts as $part)
				{
					if( !isset($curr[$part]) )
						$curr[$part] = array();

					$curr = &$curr[$part];
				}

				$curr = $v;
			}
			
			$parts = explode('\\',$name);
			$curr = &$ret;
			
			for( $i=0; $i<count($parts)-1; $i++ )
			{
				if( !isset($curr[$parts[$i]]) )
					$curr[$parts[$i]] = array();
					
				$curr = &$curr[$parts[$i]];
			}

			$curr[$parts[count($parts)-1]] = $section;
		}

		return $ret;
	}
}
}