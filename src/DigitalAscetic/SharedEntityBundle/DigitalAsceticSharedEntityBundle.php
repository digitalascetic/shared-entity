<?php

namespace DigitalAscetic\SharedEntityBundle;

use DigitalAscetic\SharedEntityBundle\DependencyInjection\Compiler\DoctrineSharedEntityPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DigitalAsceticSharedEntityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DoctrineSharedEntityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }

}
