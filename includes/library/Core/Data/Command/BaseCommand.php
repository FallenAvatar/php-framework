<?php

namespace Core\Data\Command {
	class BaseCommand extends \Core\Object {
		protected $db;
		public function __construct($db) {
			$this->db = $db;
		}
	}
}