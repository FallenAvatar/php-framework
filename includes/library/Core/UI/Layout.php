<?

namespace Core\UI
{
	class Layout extends BaseObject
	{
		public $Name;
		
		public function __construct($name)
		{
			if( !is_file($this->Application->Dirs->Layouts.$name.'.phtml') )
					throw new \Exception("Layout file for [".$name."] (".$this->Application->Dirs->Layouts.$name.'.phtml'.") not found.");
				
				$this->Name = $name;
		}
	}
}
