<?php

declare(strict_types=1);

namespace Core\Data\Statement;

class Update extends \Core\Obj {
	protected \Core\Data\Database $_db;
	protected string $_tbl;
	protected array $_vals;
	protected ?string $_where;
	protected array $_params;
	
	public function __construct(\Core\Data\Database $db, string $tbl) {
		$this->_db = $db;
		$this->_tbl = $tbl;
		$this->_vals = [];
		$this->_where = null;
		$this->_params = [];
	}
	
	public function Add(string $col, $val): void {
		$this->_vals[$col]=$val;
	}
	
	public function Where(string $where, array $ps): void {
		$this->_where = $where;
		$this->_params = array_merge($this->_params, $ps);
	}
	
	public function Execute(): int {
		$sql="UPDATE ".$this->_db->DelimTable($this->_tbl)." SET ";
		$params = [];
		
		$first=true;
		foreach( $this->_vals as $col => $val ) {
			if( !$first )
				$sql.=", ";

			$sql.=$this->_db->DelimColumn($col)." = :".preg_replace('/\s/','_',preg_replace('/([^a-zA-Z0-9]*)/','',$col));
			$params[preg_replace('/\s/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col))]=$val;
			
			$first = false;
		}
		
		if( isset($this->_where) && $this->_where != '' )
			$sql.=" WHERE ".$this->_where;
		
		return $this->_db->ExecuteNonQuery($sql,array_merge($this->_params,$params));
	}
}