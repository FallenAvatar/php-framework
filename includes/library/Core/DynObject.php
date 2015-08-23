<?php

namespace Core
{
	class DynObject extends Object implements \IteratorAggregate
	{
		protected $arrInternal;
		protected $bReadOnly;
		protected $bNewProps;

		public function IsReadOnly() { return $this->bReadOnly; }

		public function __construct($arr_props = array(), $read_only = false, $new_props = false)
		{
			$this->arrInternal = $arr_props;
			$this->bReadOnly = $read_only;
			$this->bNewProps = $new_props;
		}
		
		public function __get($name)
		{
			$ret = $this->arrInternal[$name];
			if( !isset($ret) )
			{
				throw new Exception('Property ['.$name.'] not found on Dynamic Object.');
			}
			else if( is_array( $ret ) && ArrayHelper::IsAssoc($ret) )
			{
				return new DynObject($ret);
			}
			else
				return $ret;
		}

		public function __set($name, $value)
		{
			if( $this->bReadOnly )
				throw new \Exception('This object is Read Only!');

			if( is_array($value) && ArrayHelper::IsAssoc($value) )
				$value = new DynObject($value);

			if( !isset($this->arrInternal[$name]) && !$this->bNewProps )
				throw new Exception('Property ['.$name.'] not found on Dynamic Object.');
			else
				$this->arrInternal[$name] = $value;
		}
	
		public function __isset($name)
		{
			return isset($this->arrInternal[$name]);
		}

		public function __unset($name)
		{
			if( $this->bReadOnly )
				throw new Exception('This object is Read Only!');

			unset($this->arrInternal[$name]);
		}

		public function ToArray()
		{
			$ret = array();

			foreach($this->arrInternal as $k => $v)
			{
				if( is_object($v) && ($v instanceof DynObject) )
					$ret[$k] = $v->ToArray();
				else
					$ret[$k] = $v;
			}

			return $ret;
		}
		
		public function Merge($other)
		{
			$arrOther = $other->ToArray();
			
			$this->arrInternal = array_merge_recursive($this->arrInternal, $arrOther);
			
			return $this;
		}
		
		public function getIterator()
		{
			return new DynObjectIterator($this->arrInternal);
		}
	}
}