<?php

namespace System\Web\UI\Engine
{
	class FileEnumerator implements \System\Collections\IEnumerator
	{
		protected $file;
		
		protected $curr;
		public function getCurrent() { return $this->curr; }
		
		protected function getIsEOF() { return $this->IsEOF; }
		
		public function __construct($filepath)
		{
			$this->file = new \System\IO\FileStream($filepath,'r');
			
			$this->MoveNext();
		}
		
		public function MoveNext()
		{
			$this->curr = $this->file->ReadByte();
		}
		
		public function Reset()
		{
			$this->file->Seek(0);
			$this->MoveNext();
		}
		
		public function Seek($pos)
		{
			$this->file->Seek($pos, SEEK_CUR);
		}
		
		public function ReadUntil($chars)
		{
			$ret = '';
			
			while( !in_array($this->Current, $chars) )
			{
				$ret .= $this->Current;
				$this->MoveNext();
			}
			
			return $ret;
		}
		
		public function ReadWhile($chars)
		{
			$ret = '';
			
			while( in_array($this->Current, $chars) )
			{
				$ret .= $this->Current;
				$this->MoveNext();
			}
			
			return $ret;
		}
	}
}