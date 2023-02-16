<?php

namespace DigitalAscetic\SharedEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DigitalAsceticSharedEntityBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
