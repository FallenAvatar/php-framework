<?

namespace Core\Data
{
	class SpecialValue extends \Core\Object
	{
		public static $isNull = null;
		public static $isNotNull = null;
		
		protected $name;
		public function _getName() { return $this->name; }
		public function __construct($name)
		{
			$this->name = $name;
		}
	}
	
	\Core\Data\SpecialValue::$isNull = new SpecialValue('is-null');
	\Core\Data\SpecialValue::$isNotNull = new SpecialValue('is-not-null');
}