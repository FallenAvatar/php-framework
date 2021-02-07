<?php

declare(strict_types=1);

namespace Core\Data\Statement;

class Select extends \Core\Obj {
	protected \Core\Data\Database $_db;
	protected string $_tbl;
	protected array $_cols;
	protected ?string $_where;
	protected array $_params;
	
	public function __construct(\Core\Data\Database $db, string $tbl, ?array $cols = null) {
		$this->_db = $db;
		$this->_tbl = $tbl;
		$this->_cols = $cols;
		$this->_where = null;
		$this->_params = [];
	}
	
	public function Where(string $where, array $ps = []): void {
		$this->_where = $where;
		$this->_params = array_merge($this->_params, $ps);
	}
	
	public function Execute(?string $rowClass = null): array {
		$sql='SELECT ';
		
		if( isset($this->_cols) ) {
			$first = true;
			foreach( $this->_cols as $col ) {
				if( !$first )
					$sql .= ', ';
					
				$name = $col;
				$alias = null;
				
				if( is_array($col) )
				{
					$name = $col[0];
					$alias = $col[1];
				}
				
				$sql .= $this->_db->DelimColumn($col);
				
				if( isset($alias) )
					$sql .= ' AS '.$this->_db->DelimColumn($alias);
			}
		} else
			$sql .= '*';
		
		$sql .= ' FROM '.$this->_db->DelimTable($this->_tbl);
		
		if( isset($this->_where) && $this->_where != '' )
			$sql.=' WHERE '.$this->_where;
		
		return $this->_db->ExecuteQuery($sql, $this->_params, $rowClass);
	}
}