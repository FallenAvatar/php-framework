<?php

namespace System
{
class NotSupportedException extends \System\Exception
{
	public function __construct($msg = 'That operation is not supported in the objects current state.')
	{
		parent::__construct($msg);
	}
}
}