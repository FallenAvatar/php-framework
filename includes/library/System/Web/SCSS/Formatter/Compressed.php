<?php

namespace System\Web\SCSS\Formatter
{
class Compressed extends \System\Web\SCSS\Formatter
{
	public $open = "{";
	public $tagSeparator = ",";
	public $assignSeparator = ":";
	public $break = "";

	public function indentStr($n = 0)
	{
		return "";
	}
}
}