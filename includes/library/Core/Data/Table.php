<?php declare(strict_types=1);

namespace Core\Data;

class Table extends \Core\Obj {
	private static array $_insts = [];
	public static function Get(\Core\Data\Database $db, string $tbl): Table {
		if( !($db instanceof Database) )
			$db = Database::Get($db);
			
		if( !isset(self::$_insts[$db->Name]) )
			self::$_insts[$db->Name] = [];
			
		if( isset(self::$_insts[$db->Name][$tbl]) )
			return self::$_insts[$db->Name][$tbl];
			
		$t = new Table($db, $tbl);
		
		self::$_insts[$db->Name][$tbl] = $t;
		
		return $t;
	}
	
	protected \Core\Data\Database $db;
	public function _getDb(): \Core\Data\Database { return $this->db; }
	
	protected string $tbl_name;
	public function _getName(): string { return $this->tbl_name; }
	
	protected function __construct(\Core\Data\Database $db, string $tbl) {
		$this->db = $db;
		$this->tbl_name = $tbl;
	}
	
	public function Select(?array $cols = null, ?string $where = null, ?array $ps = null, ?string $rowClass = null) {
		$stmt = new \Core\Data\Statement\Select($this->db, $this->tbl_name, $cols);
		$stmt->Where($where, $ps);
		
		return $stmt->Execute($rowClass);
	}
	
	public function InsertSingle($row): int {
		$count = $this->Insert(array($row));
		return $this->db->LastInsertId();
	}
	
	public function Insert(array $rows, ?array $cols = null): int {
		if( count($rows) <= 0 )
			return 0;
		
		$stmt = new \Core\Data\Statement\Insert($this->db, $this->tbl_name);
		foreach($rows as $row) {
			$stmt->Add($row);
		}
		
		return $stmt->Execute($cols);
	}
	
	public function Update(array $map, ?string $where = null, ?array $ps = null): int {
		$stmt = new \Core\Data\Statement\Update($this->db, $this->tbl_name);
		foreach($map as $col => $val) {
			$stmt->Add($col, $val);
		}
		$stmt->Where($where, $ps);
		
		return $stmt->Execute();
	}
	
	public function Delete(?string $where = null, ?array $ps = null): int {
		$stmt = new \Core\Data\Statement\Delete($this->db, $this->tbl_name);
		$stmt->Where($where, $ps);
		
		return $stmt->Execute();
	}
}