<?php declare(strict_types=1);

namespace Core\Data\Statement;

class Delete extends \Core\Obj {
	protected \Core\Data\Database $_db;
	protected string $_tbl;
	protected string $_where;
	protected array $_params;
	protected ?string $_paramPrefix;
	public function _setParamPrefix(string $prefix): void { $this->_paramPrefix = $prefix; }
	
	public function __construct(\Core\Data\Database $db, string $tbl) {
		$this->_db = $db;
		$this->_tbl = $tbl;
		$this->_where = null;
		$this->_params = [];
	}
	
	public function Where(string $where, array $ps): void {
		$this->_where = $where;
		$this->_params = array_merge($this->_params, $ps);
	}
	
	public function Execute(): int {
		return $this->_db->ExecuteNonQuery($this->Sql, $this->_params);
	}

	public function _getSql(): string {
		$sql="DELETE FROM ".$this->_db->DelimTable($this->_tbl);
		
		if( isset($this->_where) && $this->_where != '' )
			$sql.=" WHERE ".$this->_where;

		return $sql;
	}
}