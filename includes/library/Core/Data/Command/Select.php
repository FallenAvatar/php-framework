<?php

namespace Core\Data\Command {
	class Select extends BaseCommand {
		protected $cols;
		
		public function __construct($db, $cols) {
			parent::__construct($db);
			$this->cols = $cols;
		}
	}
}