<?php

declare(strict_types=1);

namespace Core\API;

class Base extends \Core\Web\BaseObject {
	protected function Error(string $msg): array {
		return ['error' => true, 'message' => $msg];
	}

	public function Redirect(string $uri, ?string $msg = null): array {
		return ['error' => false, 'actions' => ['type' => 'redirect', 'url' => $uri], 'message' => (isset($msg) ? $msg : null)];
	}

	protected function Refresh(?string $msg = null): array {
		return ['error' => false, 'actions' => ['type' => 'refresh'], 'message' => (isset($msg) ? $msg : null)];
	}
}