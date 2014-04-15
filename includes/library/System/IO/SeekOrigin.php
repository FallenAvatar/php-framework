<?php

namespace System\IO
{
	class SeekOrigin extends \System\Enum
	{
		const Begin = SEEK_SET;
		const Current = SEEK_CUR;
		const End = SEEK_END;
	}
}