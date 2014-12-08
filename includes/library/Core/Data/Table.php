<?

namespace Core\Data
{
	class Table extends \Core\Object
	{
		protected $db;
		protected $tbl;
		
		public function __construct($tbl, $conn = null)
		{
			$this->db = Database::GetInstance($conn);
			$this->tbl = $tbl;
		}
	}
}