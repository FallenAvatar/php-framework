<?

namespace Core
{
	class Object
	{
		public function __get(string $name)
		{
			$method_name = '_get'.$name;
			if( method_exists($this, $method_name) )
				return $this->$method_name();
			
			throw new Exception('Property ['.$name.'] not found on object.');
		}
		
		public function __set(string $name, $value)
		{
			$method_name = '_set'.$name;
			if( method_exists($this, $method_name) )
				return $this->$method_name($value);
			
			throw new Exception('Property ['.$name.'] not found on type ['.get_class($this).'].');
		}
		
		public function __call(string $name, array $args)
		{
			if( !method_exists($this, $name.'0') )
				throw new Exception('Method ['.$name.'] not found on type ['.get_class($this).'].');
			
			$methods = array();
			$idx = 0;
			while(true)
			{
				if( !method_exists($this, $name.$idx) )
					break;
					
				$m = new \ReflectionMethod($this, $name.$idx);
				$arg_list = $m->getParameters();
				
				$methods[] = array(
					'name' => $name.$idx,
					'reflect' => $m
				);
				
				$idx++;
			}
			
			// sort
			
			$method = $methods[0]['reflect'];
			
			return $method->invokeArgs($this, $args);
		}
		
		public static function __callStatic(string $name, array $args)
		{
		}
		
		public function __destruct()
		{
			$this->Dispose();
		}
		
		public function Dispose()
		{ }
		
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