<?php declare(strict_types=1);

namespace TG\Modules\Security\API\Admin;

class Group extends \Site\API\Base {
	public function Edit($name, $display_name, $desc = null, $hidden = false, $roles = [], $id = null) {
		if( !$this->Can('manage-security') )
			return array('error' => true, 'message' => 'You are not authorized.');
		
		if( isset($id) && (!is_numeric($id) || ($id = intval($id)) <= 0) )
			return array('error' => true, 'message' => 'Invalid Group ID.');
		
		$item = new \TG\Modules\Security\Data\Group($id);
		$item->name = $name;
		$item->display_name = $display_name;
		$item->desc = $desc;
		$item->hidden = $hidden ? true : false;
		$item->Save();

		$item->ClearRoles();
		
		foreach( $roles as $role )
			$item->AddRole($role);

		return array('error' => false, 'debug' => $hidden, 'message' => 'Group has been saved.', 'actions' => array(['type' => 'redirect', 'url' => '#security/groups']));
	}
	
	public function Delete($id) {
		if( !$this->Can('manage-security') )
			return array('error' => true, 'message' => 'You are not authorized.');
		
		if( !isset($id) || (!is_numeric($id) || intval($id) <= 0) )
			return array('error' => true, 'message' => 'Invalid Group ID.');
		
		$item = new \TG\Modules\Security\Data\Group(intval($id));
		$item->Delete();
		
		return array('error' => false, 'message' => 'Group has been removed.', 'actions' => array(['type' => 'remove', 'ele' => '#groups-table tr[data-id='.$id.']']));
	}
}
