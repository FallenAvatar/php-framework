<?php

namespace Core\Data\MySql {
	class Database extends \Core\Data\Database {
		protected function Connect($host,$user,$pw,$db) {
			$dsn = 'mysql:host='.$host.';port=3306;dbname='.$db.';charset=utf8';
			$options = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'	// Fix for charset being ignored prior to 5.3.6
			);
			$this->dbh = new \PDO($dsn, $user, $pw, $options);
		}
		
		public function Delim($val,$delim) {
			switch($delim) {
			case parent::Delim_Database:
			case parent::Delim_Schema:
			case parent::Delim_Table:
			case parent::Delim_Column:
				return '`'.$val.'`';
			case parent::Delim_Parameter:
				return ':'.$val;
			case parent::Delim_String:
				return "'".$val."'";
			}
			
			return $val;
		}
	}
}