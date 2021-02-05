<?php

declare(strict_types=1);

namespace Core\Handlers;

interface IRequestHandler {
	public function CanHandleRequest(\Core\Application $App): bool;
	public function ExecuteRequest(\Core\Application $App, $data): void;
}