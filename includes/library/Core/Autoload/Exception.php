<?php

namespace Core\Autoload
{
	class Exception extends \Core\SystemException
	{
		public function __construct($msg = 'An error has occured in the autoloader.')
		{
			parent::__construct($msg);
		}
	}
}