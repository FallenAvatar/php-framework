<?php

namespace Core
{
	class DynObjectIterator implements \Iterator
	{
		private $aValues;
		private $aKeys;
		private $iCurrent;
		private $iLen;
		
		public function __construct($arr)
		{
			$this->iCurrent = 0;
			$this->iLen = count($arr);
			$this->aKeys = array();
			$this->aValues = array();
			
			foreach($arr as $k => $v)
			{
				$this->aKeys[] = $k;
				$this->aValues[] = $v;
			}
		}
		
		public function current()
		{
			$v = $this->aValues[$this->iCurrent];
			
			if( is_array($v) && ArrayHelper::IsAssoc($v) )
				$v = new DynObject($v, true, false);
			
			return $v;
		}
		
		public function key()
		{
			return $this->aKeys[$this->iCurrent];
		}
		
		public function next()
		{
			$this->iCurrent++;
		}
		
		public function rewind()
		{
			$this->iCurrent = 0;
		}
		
		public function valid()
		{
			return ($this->iCurrent < $this->iLen);
		}
	}
}