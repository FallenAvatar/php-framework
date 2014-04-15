<?php

namespace System\IO
{
class FileEnumerator
{
	public $Current;
	
	protected $file;
	
	public function __construct($file)
	{
		$this->file = fopen($file,'r');
		
		$this->Advance();
	}
	
	public function IsEOF()
	{
		return feof($this->file);
	}
	
	public function Seek($pos)
	{
		fseek($this->file,$pos,SEEK_CUR);
	}
	
	public function Advance()
	{
		$this->Current=fread($this->file,1);
		//echo $this->Current;
	}
	
	public function ReadUntil($chars)
	{
		$ret='';
		
		while( !in_array($this->Current,$chars) )
		{
			$ret.=$this->Current;
			$this->Advance();
		}
		
		return $ret;
	}
	
	public function ReadWhile($chars)
	{
		$ret='';
		
		while( in_array($this->Current,$chars) )
		{
			$ret.=$this->Current;
			$this->Advance();
		}
		
		return $ret;
	}
}
}