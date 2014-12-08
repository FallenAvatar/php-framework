<?php

namespace Site\Data
{
class Site extends \Core\Data\ActiveRecord
{
	public static function FindByDomain($domain)
	{
		$db = \Core\Data\Database::Get();
		$sql = "SELECT * FROM `sites` WHERE `domain` = :d";
		
		$rows = $db->ExecuteQuery($sql, array('d' => $domain), '\Site\Data\Site');
		
		if( count($rows) != 1 )
			return null;
		
		$ret = $rows[0];
		
		return $ret;
	}
	
	public function __construct($id=null)
	{
		parent::__construct(array(
			'table' => 'sites',
			'primaryidname' => 'id',
			'columns' => array(
				'domain'
			),
			'id' => $id
		));
	}
}
}