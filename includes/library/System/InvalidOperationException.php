<?php

namespace System
{
class InvalidOperationException extends \System\Exception
{
	public function __construct($msg = 'Attempted to perform an invalid operation given the objects state.')
	{
		parent::__construct($msg);
	}
}
}