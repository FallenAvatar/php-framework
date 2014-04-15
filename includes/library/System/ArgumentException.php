<?php

namespace System
{
class ArgumentException extends \System\Exception
{
	public function __construct($msg = 'Invalid Argument provided.') {
		parent::__construct($msg);
	}
}
}