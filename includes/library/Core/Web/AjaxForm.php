<?php

declare(strict_types=1);

namespace Core\Web;

class AjaxForm extends Form {
	protected function __construct(string $id, string $action) {
		parent::__construct($id, $action);
	}
}