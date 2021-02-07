<?php

declare(strict_types=1);

namespace Core\Data\Statement;

class Insert extends \Core\Obj {
	protected \Core\Data\Database $_db;
	protected string $_tbl;
	protected array $_rows;
	
	public function __construct(\Core\Data\Database $db, string $tbl) {
		$this->_db = $db;
		$this->_tbl = $tbl;
		$this->_rows = [];
	}
	
	public function Add(array $row): void {
		$this->_rows[]=$row;
	}
	
	public function Execute(?array $cols = null): int {
		if( count($this->_rows) <= 0 )
			return 0;
		
		$sql="INSERT INTO ".$this->_db->DelimTable($this->_tbl)."(";
		$sql2=") VALUES (";
		$params=[];
		
		$firstrow = $this->_rows[0];
		$first=true;
		foreach($firstrow as $col => $val) {
			if( isset($cols) && !in_array($col, $cols) )
				continue;
				
			if( !$first )
				$sql .= ', ';
			
			$sql .= $this->_db->DelimColumn($col);
			$first = false;
		}
		
		$first=true;
		$idx=0;
		foreach( $this->_rows as $row ) {
			if( !$first )
				$sql2.="), (";

			$firstcol = true;
			foreach($row as $col => $val) {
				if( isset($cols) && !in_array($col, $cols) )
					continue;
				
				if( !$firstcol )
					$sql2 .= ', ';
					
				$colClean=preg_replace('/[^a-zA-Z0-9]*/','',preg_replace('/\s+/','_',$col)).$idx;
				$sql2.=':'.$colClean;
				$params[$colClean] = $val;
				
				$firstcol = false;
			}
			
			$first = false;
			$idx++;
		}
		
		$sql = $sql.$sql2.")";

		return $this->_db->ExecuteNonQuery($sql,$params);
	}
}