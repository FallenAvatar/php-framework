<?

namespace Core
{
	class Object
	{
		public function __get($name)
		{
			$method_name = '_get'.$name;
			if( method_exists($this, $method_name) )
				return $this->$method_name();
			
			throw new Exception('Property ['.$name.'] not found on object.');
		}
		
		public function __set($name, $value)
		{
			$method_name = '_set'.$name;
			if( method_exists($this, $method_name) )
				return $this->$method_name($value);
			
			throw new Exception('Property ['.$name.'] not found on object.');
		}
		
		public function __destruct()
		{
			if( method_exists($this, 'Dispose') )
				$this->Dispose();
		}
		
		public function Equals($oOther)
		{
			return $this == $oOther;
		}
		
		public function ReferenceEquals($oOther)
		{
			return $this === $oOther;
		}
		
		public function GetType()
		{
			return new \ReflectionClass($this);
		}
	}
}