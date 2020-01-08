<?php

namespace DigitalAscetic\SharedEntityBundle\DependencyInjection;

use DigitalAscetic\SharedEntityBundle\EventListener\SharedEntityDoctrineSubscriber;
use DigitalAscetic\SharedEntityBundle\EventListener\SharedEntitySubscriber;
use DigitalAscetic\SharedEntityBundle\Service\DoctrineSharedEntityConstructor;
use DigitalAscetic\SharedEntityBundle\Service\SharedEntityService;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DigitalAsceticSharedEntityExtension extends Extension implements PrependExtensionInterface
{

    public function load(array $configs, ContainerBuilder $container)
    {

        $config = $this->processConfiguration(new Configuration(), $configs);

        if (isset($config['enabled']) && $config['enabled']) {

            // SharedService
            $sharedEntityServ = new Definition(SharedEntityService::class);
            $sharedEntityServ->addArgument(new Reference('doctrine.orm.entity_manager'));
            $sharedEntityServ->addArgument($config['origin']);
            $sharedEntityServ->setPublic(true);
            $container->setDefinition('digital_ascetic.shared_entity_service', $sharedEntityServ);
            $container->setAlias(SharedEntityService::class, 'digital_ascetic.shared_entity_service');

            // SharedEntity events handler
            $sharedEntitySub = new Definition(
              SharedEntitySubscriber::class
            );
            $sharedEntitySub->addArgument(new Reference('service_container'));
            $sharedEntitySub->addArgument($config['origin']);
            $sharedEntitySub->addArgument(new Reference('doctrine.orm.entity_manager'));
            $sharedEntitySub->addTag(
              'kernel.event_listener',
              array('event' => 'digital_ascetic.shared.entity.persist', 'method' => 'onSharedEntityPersist')
            );
            $container->setDefinition(
              'digital_ascetic.shared_entity.persist_subscriber',
              $sharedEntitySub
            );

            
            // SharedEntity Doctrine Subscriber, handles SharedEntity events on Doctrine
            // SharedEntity events
            $sharedEntityDctr = new Definition(
              SharedEntityDoctrineSubscriber::class
            );
            $sharedEntityDctr->addArgument(new Reference('event_dispatcher'));
            $sharedEntityDctr->addArgument($config['index_source']);
            $sharedEntityDctr->setPublic(false);
            $sharedEntityDctr->addTag('doctrine.event_subscriber');
            $container->setDefinition(
              'digital_ascetic.shared_entity.sharedentity_doctrine',
              $sharedEntityDctr
            );


            // SharedEntity JMSSerializer ObjectConstructor
            $sharedEntityConstr = new Definition(
              DoctrineSharedEntityConstructor::class
            );
            $sharedEntityConstr->addArgument(new Reference('doctrine'));
            $sharedEntityConstr->addArgument(new Reference('jms_serializer.unserialize_object_constructor'));
            $sharedEntityConstr->addArgument(new Reference('digital_ascetic.shared_entity_service'));
            $sharedEntityConstr->addArgument(new Reference('logger'));
            $sharedEntityConstr->setPublic(false);
            $container->setDefinition(
              'digital_ascetic.doctrine_shared_entity_constructor',
              $sharedEntityConstr
            );
            $container->setAlias(
              'jms_serializer.object_constructor',
              'digital_ascetic.doctrine_shared_entity_constructor'
            );

        }


    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {

        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['FrameworkBundle'])) {
            throw new InvalidConfigurationException(
              "You must register FrameworkBundle in AppKernel in order to work with SharedEntityBundle"
            );
        }

        if (!isset($bundles['DoctrineBundle'])) {
            throw new InvalidConfigurationException(
              "You must register DoctrineBundle in AppKernel in order to work with SharedEntityBundle"
            );
        }

    }

}
