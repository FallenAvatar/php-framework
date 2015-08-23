<?

namespace Core\Data
{
	class LitervalValue extends SpecialValue
	{
		public $TextValue;
		
		public function __construct($name, $val)
		{
			parent::__construct($name);
			$this->TextValue = $val;
		}
	}
}