<?php

namespace System\Logging
{
	class ErrorLevel extends \System\Enum
	{
		public const Debug = 1;
		public const Info = 2;
		public const Warning = 4;
		public const Error = 8;
		public const Critical = 16;
	}
}