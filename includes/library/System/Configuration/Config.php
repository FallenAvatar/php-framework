<?php

namespace System\Configuration
{
class Config extends \System\DynObject
{
	public function __construct($config)
	{
		parent::__construct($config, true);
	}
}
}