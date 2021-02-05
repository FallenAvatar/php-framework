<?php

namespace Mandrill;

class Internal {
    public function __construct(Manager $master) {
        $this->master = $master;
    }

}


