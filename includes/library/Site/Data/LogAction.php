<?php

namespace Site\Data
{
class LogAction extends \System\Data\ActiveRecord
{
	public static function Log($msg, $user=null)
	{
		if( !isset($user) )
			$user = \Site\Data\User::GetUser();

		$la = new \Site\Data\LogAction();
		$la->dt = time();
		$la->user_id = $user->id;
		$la->message = $msg;
		$la->Save();
	}

	public static function GetLatest()
	{
		$db = \System\Data\Database::GetInstance();
		$sql = 'SELECT * FROM '.$db->Delim('log_actions',\System\Data\Database::Delim_Table).' ORDER BY '.$db->Delim('dt',\System\Data\Database::Delim_Column).' DESC LIMIT 5';
		$params = array();
		return $db->ExecuteQuery($sql,$params,'\Site\Data\LogAction');
	}

	public function __construct($id=null)
	{
		parent::__construct(array(
			'table' => 'log_actions',
			'primaryidname' => 'id',
			'columns' => array(
				'dt',
				'user_id',
				'message'
			),
			'id' => $id
		));
	}
}
}