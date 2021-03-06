<?php

declare(strict_types=1);

$this->DisableLayout();

echo \Security\System::IsLoggedIn() ? 'true' : 'false';