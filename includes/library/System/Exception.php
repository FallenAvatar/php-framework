<?php

namespace System
{
class Exception extends \Exception
{
	public function __construct($msg)
	{
		parent::__construct($msg);
	}
}
}