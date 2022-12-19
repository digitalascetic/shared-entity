<?php

namespace DigitalAscetic\SharedEntityBundle\EventListener;

use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SharedEntityPersistSubscriber
 * @package DigitalAscetic\SharedEntityBundle\EventListener
 */
class SharedEntitySubscriber implements EventSubscriberInterface
{
    private string $origin;

    private EntityManagerInterface $em;

    /**
     * SharedEntityPersistSubscriber constructor.
     * @param string $origin
     * @param EntityManagerInterface $em
     */
    public function __construct(string $origin, EntityManagerInterface $em)
    {
        $this->origin = $origin;
        $this->em = $em;
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

        $this->em->persist($sharedEntity);
        $this->em->flush();
    }

}
