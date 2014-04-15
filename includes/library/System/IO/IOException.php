<?php

namespace System\IO
{
class IOException extends \System\SystemException
{
	public function __construct($msg = 'A problem has occured with an IO operation.')
	{
		parent::__construct($msg);
	}
}
}