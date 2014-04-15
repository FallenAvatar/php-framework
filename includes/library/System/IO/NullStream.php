<?php

namespace System\IO
{
class NullStream extends \System\IO\Stream
{
	protected function getCanRead() { return true; }
	protected function getCanSeek() { return true; }
	protected function getCanWrite() { return true; }
	protected function getLength() { return 0; }
	protected function getPosition() { return 0; }
	protected function setPosition($value) { return; }
	
	public function Flush() { }
	public function Read(&$buffer, $iOffset, $iCount) { return 0; }
	public function Seek($lOffset) { return 0; }
	public function Write($buffer, $iOffset, $iCount) { return; }
}
}