<?php

namespace Site\API\Security {
	class Role extends \Site\API\Base {
		public function Edit($name, $display_name, $id = null) {
			if( !$this->Can('manage-security-roles') )
				return array('error' => true, 'message' => 'You are not authorized.');
			
			$item = null;
			if( isset($id) && (!is_numeric($id) || ($id = intval($id)) <= 0) )
				return array('error' => true, 'message' => 'Invalid Role ID.');

			$item = new \Site\Data\Security\Role($id);
			$item->name = $name;
			$item->display_name = $display_name;
			$item->Save();

			return array('error' => false, 'message' => 'Role has been saved.', 'actions' => array(['type' => 'redirect', 'url' => '#security/roles']));
		}
		
		public function Delete($id) {
			if( !$this->Can('manage-security-roles') )
				return array('error' => true, 'message' => 'You are not authorized.');
			
			if( !isset($id) || (!is_numeric($id) || intval($id) <= 0) )
				return array('error' => true, 'message' => 'Invalid Role ID.');
			
			$item = new \Site\Data\Security\Role(intval($id));
			$item->Delete();
			
			return array('error' => false, 'message' => 'Role has been removed.', 'actions' => array(['type' => 'remove', 'ele' => '#roles-table tr[data-id='.$id.']']));
		}
	}
}
