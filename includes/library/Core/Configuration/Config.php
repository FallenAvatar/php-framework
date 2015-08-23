<?php

namespace Core\Configuration
{
	class Config extends \Core\DynObject
	{
		protected $files;
		public function __construct($config, $files = null)
		{
			parent::__construct($config, true);
			
			$this->files = $files;
		}
	}
}