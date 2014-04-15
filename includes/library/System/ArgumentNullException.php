<?php

namespace System
{
class ArgumentNullException extends \System\ArgumentException
{
	public function __construct($msg = 'Argument can not be null!')
	{
		parent::__construct($msg);
	}
}
}