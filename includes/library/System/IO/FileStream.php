<?php

namespace System\IO
{
class FileStream extends \System\IO\Stream
{
	protected $file_handle;
	protected $buffer_size = 512;
	
	protected function getCanRead() { return true; }
	protected function getCanSeek() { return true; }
	protected function getCanWrite() { return true; }
	protected function getLength() { return filesize($this->file_handle); }
	protected function getPosition() { return ftell($this->file_handle); }
	protected function setPosition($value) { $this->Seek($value, SeekOrigin::Begin); }
	
	protected function getIsEOF() { return feof($this->file_handle); }
	
	public function __construct()
	{
		$args = func_get_args();
		
		if( is_resource($args[0]) )
		{
			$this->file_handle = $args[0];
			
			if( isset($args[1]) && is_numeric($args[1]) )
				$this->buffer_size = intval($args[1]);
		}
		else
		{
			$path = $args[0];
			$mode = $args[1];
			
			$this->file_handle = fopen($path, $mode.'b', false, null);
			
			if( isset($args[2]) && is_numeric($args[2]) )
				$this->buffer_size = intval($args[2]);
		}
	}
	
	public function Close()
	{
		parent::Close();
		fclose($this->file_handle);
	}
	
	public function Flush()
	{
		fflush($this->file_handle);
	}
	
	public function Read(array &$buffer, $iOffset, $iCount)
	{
		if( !isset($buffer) )
			throw new \System\ArgumentNullException('Parameter [buffer] can not be null.');
			
		if( $iOffset < 0 )
			throw new \System\ArgumentOutOfRangeException('Parameter [iOffset] can not be less than zero.');
			
		if( $iCount < 0 )
			throw new \System\ArgumentOutOfRangeException('Parameter [iCount] can not be less than zero.');
			
		if( !$this->CanRead )
			throw new \System\NotSupportedException('The Stream does not support reading.');
			
		if( count($buffer) < ($iOffset + $iCount) )
			throw new \System\ArgumentException('[iOffset] and [iCount] describe and invalid range in [buffer].');
			
		if( !$this->IsOpen )
			throw new \System\ObjectDisposedException('Methods were called after the stream was closed.');
			
		$data = fread($this->file_handle, $iCount);
		
		if( $data === false )
			throw new IOException('An I/O error occurred.');
		
		$len = min($iCount, count($data));
		
		for($i=0; $i<$len; $i++ )
			$buffer[$iOffset+$i] = $data[$i];
			
		return $len;
	}
	
	public function Seek($lOffset, $eOrigin = SEEK_SET)
	{
		if( !this->CanSeek )
			throw new \System\NotSupportedException('The stream does not support seeking.');
			
		if( !$this->IsOpen )
			throw new \System\ObjectDisposedException('Methods were called after the stream was closed.');
		
		$ret = fseek($this->file_handle, $lOffset, $eOrigin);
		
		if( $ret != 0 )
			throw new IOException('An I/O error occurred.');
			
		return $this->Position;
	}
	
	public function Write($buffer, $iOffset, $iCount)
	{
		if( !isset($buffer) )
			throw new \System\ArgumentNullException('Parameter [buffer] can not be null.');
			
		if( $iOffset < 0 )
			throw new \System\ArgumentOutOfRangeException('Parameter [iOffset] can not be less than zero.');
			
		if( $iCount < 0 )
			throw new \System\ArgumentOutOfRangeException('Parameter [iCount] can not be less than zero.');
			
		if( !$this->CanWrite )
			throw new \System\NotSupportedException('The current stream does not support writing.');
			
		if( count($buffer) < ($iOffset + $iCount) )
			throw new \System\ArgumentException('[iOffset] and [iCount] describe and invalid range in [buffer].');
			
		if( !$this->IsOpen )
			throw new \System\ObjectDisposedException('Methods were called after the stream was closed.');
			
		$data = array_slice($buffer, $iOffset, $iCount);
		$written = -1;
		
		while( $iCount > 0 && $written !== 0 && $written !== false )
		{
			$written = fwrite($this->file_handle, $data, $iCount);
			$iCount -= $written;
		}
		
		if( $written === 0 || $written === false )
			throw new IOException('An I/O error occurred.');
	}
}