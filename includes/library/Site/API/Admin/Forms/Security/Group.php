<?php

namespace Site\API\Admin\Forms\Security {
	class Group extends \Core\Web\BaseObject {
		public function Edit($name, $display_name, $id = null) {
			if( isset($id) && trim($id) == '' )
				$id = null;
			
			if( isset($id) && ($id = intval($id)) <= 0 )
				return array('error' => true, 'message' => 'Invalid group ID.');
			
			$item = new \Site\Data\Security\Group($id);
			$item->name = $name;
			$item->display_name = $display_name;
			$item->Save();
			
			return array('error' => false, 'message' => 'Group '.(isset($id) ? 'updated' : 'created').' successfuly.', 'redirect' => '/admin/security/groups/list');
		}
	}
}