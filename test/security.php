<?php

declare(strict_types=1);

$this->SetLayout(null);

echo \Security\System::IsLoggedIn() ? 'true' : 'false';