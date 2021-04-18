<?php declare(strict_types=1);

namespace Core\Data\Statement;

class InsertSingle extends \Core\Obj {
	protected \Core\Data\Database $_db;
	protected string $_tbl;
	protected array $_vars;
	
	public function __construct(\Core\Data\Database $db, string $tbl) {
		$this->_db = $db;
		$this->_tbl = $tbl;
		$this->_vars = [];
	}
	
	public function Add(string $col, $val): void {
		$this->_vars[$col] = $val;
	}
	
	public function Execute(): int {
		$sql="INSERT INTO ".$this->_db->DelimTable($this->_tbl)."(";
		$sql2=") VALUES (";
		$params=[];
		
		$first=true;
		foreach( $this->_vars as $col => $val ) {
			if( !$first ) {
				$sql.=", ";
				$sql2.=", ";
			}
			$sql.=$this->_db->DelimColumn($col);
			$colClean=preg_replace('/\s+/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col));
			$sql2.=':'.$colClean;
			$params[$colClean] = $val;
			
			$first = false;
		}
		
		$sql = $sql.$sql2.")";
		
		return $this->_db->ExecuteNonQuery($sql,$params);
	}
}