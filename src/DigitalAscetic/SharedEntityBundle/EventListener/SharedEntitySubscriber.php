<?php

namespace DigitalAscetic\SharedEntityBundle\EventListener;

use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SharedEntityPersistSubscriber
 * @package DigitalAscetic\SharedEntityBundle\EventListener
 */
class SharedEntitySubscriber implements EventSubscriberInterface
{

    /** @var  ContainerInterface */
    private $container;

    /** @var string */
    private $origin;

    /**
     * SharedEntityPersistSubscriber constructor.
     * @param ContainerInterface $container
     * @param string $origin
     */
    public function __construct(ContainerInterface $container, $origin)
    {
        $this->container = $container;
        $this->origin = $origin;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array('digital_ascetic.shared.entity.persist' => 'onSharedEntityPersist');
    }

    public function onSharedEntityPersist(SharedEntityEvent $event)
    {
        $sharedEntity = $event->getEntity();
        $source = new Source($this->origin, $sharedEntity->getId());
        $sharedEntity->setSource($source);
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($sharedEntity);
        $em->flush();
    }

}
