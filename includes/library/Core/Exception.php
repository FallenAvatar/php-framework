<?php

namespace Core
{
	class Exception extends \Exception
	{
		public function __construct($msg)
		{
			parent::__construct($msg);
		}
	}
}