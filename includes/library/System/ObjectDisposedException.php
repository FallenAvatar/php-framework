<?php

namespace System
{
class ObjectDisposedException extends \System\InvalidOperationException
{
	public function __construct($msg = 'The object has already been disposed.')
	{
		parent::__construct($msg);
	}
}
}