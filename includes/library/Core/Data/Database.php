<?php

namespace Core\Data {
	abstract class Database extends \Core\Obj {
		const Delim_None = 0;
		const Delim_Database = 1;
		const Delim_Schema = 2;
		const Delim_Table = 3;
		const Delim_Column = 4;
		const Delim_Parameter = 5;
		const Delim_String = 6;

		private static $_insts=[];
		public static function Get($conn = null) {
			$app = \Core\Application::Get();

			if( !isset($conn) )
				$conn = $app->Config->Core->Data->defaultConnectionName;

			if( !isset(self::$_insts[$conn]) ) {
				$adapter = "\\Core\\Data\\".$app->Config->Database->$conn->driver."\\Database";
				$app = \Core\Application::Get();
				self::$_insts[$conn] = new $adapter($conn, $app->Config->Database->$conn);
			}

			return self::$_insts[$conn];
		}

		protected $db_name;
		public function _getName() {
			return $this->db_name;
		}
		protected $dbh;

		public function __construct($name, $creds) {
			$this->db_name = $name;
			record_timing(__CLASS__.'::'.__FUNCTION__.' - PreConnect');
			$this->Connect($creds->host, $creds->user, $creds->pass, $creds->db_name);
			record_timing(__CLASS__.'::'.__FUNCTION__.' - PostConnect');

			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
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

		public function ExecuteScript($sql) {
			$utf8bom = "\xef\xbb\xbf";
			if( \startsWith($sql, $utf8bom) )
				$sql = mb_substr($sql,3);

			$sth = $this->dbh->prepare($sql);
			$sth->execute();

			$ret = [];
			$i = 0;

			do {
				$rs = null;
				$colCount = $sth->columnCount();
				$rowCount = $sth->rowCount();

				if( $colCount == 0 ) {
					$rs = $sth->rowCount();
				} else if( $colCount == 1 && $rowCount == 1 ) {
					$t = $sth->fetch(\PDO::FETCH_NUM);

					if( $t !== false )
						$rs = $t[0];
				} else
					$rs = $sth->fetchAll(\PDO::FETCH_ASSOC);

				$ret[] = $rs;
			} while( (++$i >= 0) && $sth->nextRowset() );

			return $ret;
		}

		protected function BindParams(&$sth, array $params) {
			foreach( $params as $n => $p ) {
				$t = \PDO::PARAM_STR;
				if( !isset($p) )
					$t = \PDO::PARAM_NULL;
				else if( is_bool($p) )
					$t = \PDO::PARAM_BOOL;
				else if( is_numeric($p) )
					$t = \PDO::PARAM_INT;
				else if( $p instanceof SpecialValue ) {
					switch( $p->Name ) {
					/*case 'is-null':
						$ret .= ' IS NULL';
						return $ret;
					case 'is-not-null':
						$ret .= ' IS NOT NULL';
						return $ret;
					case "lit-value":
						$ret .= ' = ' . $v->TextValue;
						return $ret;*/
					case "func-value":
						$p = $p->ParamValue;
						break;
					case "like":
						$p = $p->Value;
						break;
					default:
						throw new \Exception( 'Unhandled Special Value ['.get_class($p).'].' );
					}
				}

				$sth->bindValue(':'.$n, $p, $t);
			}
		}

		public function ExecuteMultiple( $sql, $params = [], $opts = [] ) {
			$sth = $this->dbh->prepare($sql);
			$this->BindParams($sth, $params);
			$sth->execute();

			$ret = [];
			$i = 0;

			do {
				$rows = null;

				if( isset($opts[$i]) && is_string($opts[$i]) ) {
					if( $opts[$i] == 's' || $opts[$i] == 'scalar' ) {
						$t = $sth->fetch(\PDO::FETCH_NUM);

						if( $t !== false )
							$rows = $t[0];
					} else if( $opts[$i] == 'nq' || $opts[$i] == 'nonquery' ) {
						$rows = $sth->rowCount();
					} else {
						$t = $sth->fetchAll(\PDO::FETCH_ASSOC);
						$rows = [];
						$rowClass = $opts[$i];

						foreach($t as $row)
							$rows[] = new $rowClass($row);
					}
				} else
					$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

				$ret[] = $rows;
			} while( (++$i >= 0) && $sth->nextRowset() );

			return $ret;
		}

		public function ExecuteQuery($sql, $params=[], $rowClass=null) {
			$sth = $this->dbh->prepare($sql);
			$this->BindParams($sth, $params);
			$sth->execute();
			$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

			if( !isset($rowClass) || trim($rowClass) == '' )
				return $rows;

			$ret = [];
			foreach($rows as $row)
				$ret[] = new $rowClass($row);

			return $ret;
		}

		public function ExecuteNonQuery($sql, $params=[]) {
			$sth = $this->dbh->prepare($sql);
			$this->BindParams($sth, $params);
			$sth->execute();

			return $sth->rowCount();
		}

		public function ExecuteScalar($sql, $params=[]) {
			$sth = $this->dbh->prepare($sql);
			$this->BindParams($sth, $params);
			$sth->execute();
			$ret = $sth->fetch(\PDO::FETCH_NUM);

			if( $ret !== false )
				return $ret[0];

			return null;
		}

		public function LastInsertId() {
			return $this->dbh->lastInsertId();
		}

		public function RowCount() {
			return $this->dbh->query('SELECT FOUND_ROWS()')->fetchColumn();
		}

		public function StartTransaction() {
			$this->dbh->beginTransaction();
		}

		public function CommitTransaction() {
			$this->dbh->commit();
		}

		public function RollbackTransaction() {
			$this->dbh->rollBack();
		}
	}
}