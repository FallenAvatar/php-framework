<?php

namespace Core\Configuration
{
	class ConfigurationException extends \Core\SystemException
	{
		public function __construct($msg = 'A configuration error has been detected.')
		{
			parent::__construct($msg);
		}
	}
}