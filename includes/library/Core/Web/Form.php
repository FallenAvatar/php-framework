<?php declare(strict_types=1);

namespace Core\Web;

class Form extends \Core\Obj {
	protected string $id;
	public function _getID(): string { return $this->id; }

	protected string $action;
	public function _getAction(): string { return $this->action; }

	protected function __construct(string $id, string $action) {
		$this->id = $id;
		$this->action = $action;
	}

	public function __toString(): string {
		ob_start();

?>
	<form id="<?=$this->id?>" action="#" method="post" class="form">
	</form>
<?php

		return ob_get_clean();
	}
}