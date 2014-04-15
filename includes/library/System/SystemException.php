<?php

namespace System
{
class SystemException extends \System\Exception
{
	public function __construct($msg = 'A system error has occured.')
	{
		parent::__construct($msg);
	}
}
}