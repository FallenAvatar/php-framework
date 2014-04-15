<?php

namespace System\IO
{
abstract class Stream extends \System\Object implements \System\IDisposable
{
	protected $isOpen = false;
	protected function getIsOpen() { return $this->isOpen; }
	
	protected $readTimeout = null;
	protected $writeTimeout = null;
	protected function getCanTimeout() { return (isset($this->readTimeout) || isset($this->writeTimeout)); }
	
	protected function getWriteTimeout() { return $this->writeTimeout; }
	protected function setWriteTimeout($value) { $this->writeTimeout = $value; }
	
	protected function getReadTimeout() { return $this->readTimeout; }
	protected function setReadTimeout($value) { $this->readTimeout = $value; }
	
	protected abstract function getCanRead();
	protected abstract function getCanSeek();
	protected abstract function getCanWrite();
	protected abstract function getLength();
	protected abstract function getPosition();
	protected abstract function setPosition($value);
	
	protected function __construct()
	{
		$this->isOpen = true;
	}
	
	public function Close()
	{
		$this->isOpen = false;
	}
	
	public function CopyTo(\System\IO\Stream $oDestination, $iBufferSize = null)
	{
		if( !isset($oDestination) )
			throw new \System\ArgumentNullException('Destination can not be null.');
		
		if( !$this->IsOpen )
			throw new \System\ObjectDisposedException('Source Stream is disposed.');
		
		if( !$oDestination->IsOpen )
			throw new \System\ObjectDisposedException('Destination Stream is disposed.');
		
		if( isset($iBufferSize) )
		{
			if( !is_int($iBufferSize) )
				throw new \System\ArgumentException('BufferSize must be an integer.');
			
			if( $iBufferSize <= 0 )
				throw new \System\ArgumentOutOfRangeException('BufferSize can not be less than zero.');
		}
		
		if( !$this->CanRead )
			throw new \System\NotSupportedException('Source stream can not be read from.');
		
		if( !$oDestination->CanWrite )
			throw new \System\NotSupportedException('Destination stream can not be written to.');
	}
	
	public function Dispose()
	{
		if( $this->IsOpen )
			$this->Close();
	}
	
	public function ReadByte()
	{
		if( !$this->CanRead )
			throw new \System\NotSupportedException('Stream can not be read from.');
			
		$ret = array();
		$this->Read(&$ret, 0, 1);
		
		return $ret[0];
	}
	
	public function WriteByte($cValue)
	{
		if( !$this->CanWrite )
			throw new \System\NotSupportedException('Stream can not be written to.');

		$this->Write(array($cValue), 0, 1);
	}
	
	public abstract function Flush();
	public abstract function Read(&$buffer, $iOffset, $iCount);
	public abstract function Seek($lOffset);
	public abstract function Write($buffer, $iOffset, $iCount);
}
}