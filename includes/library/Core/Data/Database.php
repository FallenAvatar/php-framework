<?php

namespace Core\Data {
	abstract class Database extends \Core\Object {
		const Delim_None = 0;
		const Delim_Database = 1;
		const Delim_Schema = 2;
		const Delim_Table = 3;
		const Delim_Column = 4;
		const Delim_Parameter = 5;
		const Delim_String = 6;
		
		private static $_insts=array();
		public static function Get($conn = null) {
			$app = \Core\Application::GetInstance();
			
			if( !isset($conn) )
				$conn = $app->Config->Core->Data->defaultConnectionName;
			
			if( !isset(self::$_insts[$conn]) ) {
				$adapter = "\\Core\\Data\\".$app->Config->Database->$conn->driver."\\Database";
				$app = \Core\Application::GetInstance();
				self::$_insts[$conn] = new $adapter($conn, $app->Config->Database->$conn);
			}
			
			return self::$_insts[$conn];
		}
		
		protected $db_name;
		public function _getName() { return $this->db_name; }
		protected $dbh;
		
		public function __construct($name, $creds) {
			$this->db_name = $name;
			$this->Connect($creds->host,$creds->user,$creds->pass,$creds->db_name);
			
			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, TRUE);
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		
		protected abstract function Connect($host,$user,$pw,$db);
		public abstract function Delim($val,$delim);
		
		public function DelimDatabase($val) { return $this->Delim($val, self::Delim_Database); }
		public function DelimSchema($val) { return $this->Delim($val, self::Delim_Schema); }
		public function DelimTable($val) { return $this->Delim($val, self::Delim_Table); }
		public function DelimColumn($val) { return $this->Delim($val, self::Delim_Column); }
		public function DelimParameter($val) { return $this->Delim($val, self::Delim_Parameter); }
		public function DelimString($val) { return $this->Delim($val, self::Delim_String); }
		
		public function ExecuteQuery($sql,$params=array(),$rowClass=null) {
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
			
			if( !isset($rowClass) || trim($rowClass) == '' )
				return $rows;
				
			$ret = array();
			foreach($rows as $row)
				$ret[] = new $rowClass($row);
				
			return $ret;
		}
		
		public function ExecuteNonQuery($sql,$params=array()) {
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			return $sth->rowCount();
		}
		
		public function ExecuteScalar($sql,$params=array()) {
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			$ret = $sth->fetch(\PDO::FETCH_NUM);
			if( $ret !== FALSE )
				return $ret[0];
				
			return NULL;
		}
		
		public function ExecuteMultiple( array $stmts ) {
			
			$sql = '';
			$params = array();
			$i = 0;
			
			foreach($stmts as $stmt) {
				$stmt->ParamPerfix = 'i'.$i.'_';
				$sql .= $stmt->Sql;
				$params = array_merge($params, $stmt->Params);
				$i++;
			}
			
			$ret = array();
			
			foreach($stmts as $id => $stmt) {
				
			}
			
			return $ret;
		}
		
		public function LastInsertId() {
			return $this->dbh->lastInsertId();
		}
	}
}