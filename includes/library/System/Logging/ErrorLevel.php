<?php

namespace System\Logging
{
	class ErrorLevel extends \System\Enum
	{
		const Debug = 1;
		const Info = 2;
		const Warning = 4;
		const Error = 8;
		const Critical = 16;
	}
}