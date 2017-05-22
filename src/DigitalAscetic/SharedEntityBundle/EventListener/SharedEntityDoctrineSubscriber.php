<?php

namespace DigitalAscetic\SharedEntityBundle\EventListener;

use DigitalAscetic\SharedEntityBundle\Entity\BaseSharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SharedEntityDoctrineSubscriber
 * @package DigitalAscetic\SharedEntityBundle\EventListener
 */
class SharedEntityDoctrineSubscriber implements EventSubscriber
{

    /** @var  EventDispatcherInterface */
    private $dispatcher;

    /** @var  boolean */
    private $addIndexForSource;

    /**
     * SharedEntityDoctrineSubscriber constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param bool $addIndexForSource
     */
    public function __construct(EventDispatcherInterface $dispatcher, $addIndexForSource)
    {
        $this->dispatcher = $dispatcher;
        $this->addIndexForSource = $addIndexForSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
          Events::postPersist,
          Events::loadClassMetadata,
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        $reflectionClass = $metadata->getReflectionClass();

        if (!$reflectionClass->isSubclassOf(BaseSharedEntity::class) || !$this->addIndexForSource) {
            return;
        }

        $indexes = isset($metadata->table['indexes']) ? $metadata->table['indexes'] : array();

        $indexes[$metadata->getTableName().'_source_idx'] = array(
          'columns' => array('source_origin', 'source_id'),
        );

        $metadata->table['indexes'] = $indexes;

        $metadata->setPrimaryTable($metadata->table);

    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var SharedEntity $entity */
        $entity = $args->getEntity();

        if ($this->isSharedEntity($entity)) {

            if (!$entity->getSource()) {
                // Updating the source on persist is delegated to SharedEntityPersistSubscriber
                // Officially we can't use a persist+flush on a postPersist event (even though it was apparently
                // working) so we delegate this to a different (non doctrine) subscriber.
                // Not 100% clear why we have to do that and if this grants us total safety with this)
                $this->dispatcher->dispatch('digital_ascetic.shared.entity.persist', new SharedEntityEvent($entity));
            }
        }
    }

    private function isSharedEntity($entity)
    {
        return ($entity instanceof SharedEntity);
    }

}
