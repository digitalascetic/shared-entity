<?php

namespace DigitalAscetic\SharedEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineSharedEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('digital_ascetic.doctrine_shared_entity_constructor')
            ->setDecoratedService('jms_serializer.object_constructor')
            ->replaceArgument(1, new Reference('digital_ascetic.doctrine_shared_entity_constructor.inner'))
            ->setPublic(false);
    }
}