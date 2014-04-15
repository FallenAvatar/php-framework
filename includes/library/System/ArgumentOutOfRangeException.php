<?php

namespace System
{
class ArgumentOutOfRangeException extends \System\ArgumentException
{
	public function __construct($msg = 'Argument is out of range.')
	{
		parent::__construct($msg);
	}
}
}