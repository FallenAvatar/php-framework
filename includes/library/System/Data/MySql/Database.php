<?php

namespace System\Data\MySql
{
	class Database extends \System\Data\Database
	{
		protected function Connect($host,$user,$pw,$db)
		{
			$dsn = 'mysql:host='.$host.';port=3306;dbname='.$db;
			$this->dbh = new \PDO($dsn,$user,$pw);
			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, TRUE);
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		
		public function ExecuteQuery($sql,$params=array(),$tableClass='')
		{
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
			
			if( !isset($tableClass) || trim($tableClass) == '' )
				return $rows;
				
			$ret = array();
			foreach($rows as $row)
				$ret[] = new $tableClass($row);
				
			return $ret;
		}
		
		public function ExecuteNonQuery($sql,$params=array())
		{
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			return $sth->rowCount();
		}
		
		public function ExecuteScalar($sql,$params=array())
		{
			$sth = $this->dbh->prepare($sql);
			$sth->execute($params);
			$ret = $sth->fetch(\PDO::FETCH_NUM);
			if( $ret !== FALSE )
				return $ret[0];
				
			return NULL;
		}
		
		public function Escape($val,$delim=parent::Delim_None)
		{
			$ret = $this->dbh->quote($val);
			
			if( $delim != parent::Delim_None )
				$ret = $this->Delim($ret);
				
			return $ret;
		}
		
		public function Delim($val,$delim)
		{
			switch($delim)
			{
				case parent::Delim_Table:
					return '`'.$val.'`';
				case parent::Delim_Column:
					return '`'.$val.'`';
				case parent::Delim_String:
					return "'".$val."'";
			}
			
			return $val;
		}
		
		public function LastInsertId()
		{
			return $this->dbh->lastInsertId();
		}
	}
}