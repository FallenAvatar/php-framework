<?php

namespace System\Configuration
{
class ConfigurationException extends \System\SystemException
{
	public function __construct($msg = 'A configuration error has been detected.')
	{
		parent::__construct($msg);
	}
}
}