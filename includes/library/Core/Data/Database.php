<?

namespace Core\Data
{
	abstract class Database extends \Core\Object
	{
		const Delim_None = 0;
		const Delim_Table = 1;
		const Delim_Column = 2;
		const Delim_String = 3;
		
		private static $_insts=array();
		public static function GetInstance($conn = 'default')
		{
			$app = \Core\Application::GetInstance();
			
			if( !isset(self::$_insts[$conn]) )
			{
				$adapter = "\\Core\\Data\\".$app->Config->Database->$conn->driver."\\Database";
				$app = \Core\Application::GetInstance();
				self::$_insts[$conn] = new $adapter($app->Config->Database->$conn);
			}
			
			return self::$_insts[$conn];
		}
		
		protected $dbh;
		
		public function __construct($creds)
		{
			$this->Connect($creds->host,$creds->user,$creds->pass,$creds->db_name);
		}
		
		protected abstract function Connect($host,$user,$pw,$db);
		
		public abstract function ExecuteQuery($sql,$params=array(),$tableClass='');
		public abstract function ExecuteNonQuery($sql,$params=array());
		public abstract function ExecuteScalar($sql,$params=array());
		public abstract function Escape($val,$delim=self::DELIM_NONE);
		public abstract function Delim($val,$delim);
		public abstract function LastInsertId();
	}
}