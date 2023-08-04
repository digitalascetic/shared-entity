<?php

namespace DigitalAscetic\SharedEntityBundle\EventListener;

use DigitalAscetic\SharedEntityBundle\Entity\BaseSharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SharedEntityDoctrineSubscriber
 * @package DigitalAscetic\SharedEntityBundle\EventListener
 */
class SharedEntityDoctrineSubscriber
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
    public function __construct(EventDispatcherInterface $dispatcher, bool $addIndexForSource)
    {
        $this->dispatcher = $dispatcher;
        $this->addIndexForSource = $addIndexForSource;
    }


    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        $reflectionClass = $metadata->getReflectionClass();

        if (!$reflectionClass->isSubclassOf(BaseSharedEntity::class) || !$this->addIndexForSource) {
            return;
        }

        $indexes = $metadata->table['indexes'] ?? array();

        $indexes[$metadata->getTableName() . '_source_idx'] = array(
            'columns' => array('source_origin', 'source_id'),
        );

        $metadata->table['indexes'] = $indexes;

        $metadata->setPrimaryTable($metadata->table);

    }

    /**
     * @param PostPersistEventArgs $args
     */
    public function postPersist(PostPersistEventArgs $args)
    {

        /** @var SharedEntity $entity */
        $entity = $args->getObject();
        if ($this->isSharedEntity($entity)) {
            if (!$entity->getSource()) {
                // Updating the source on persist is delegated to SharedEntityPersistSubscriber
                // Officially we can't use a persist+flush on a postPersist event (even though it was apparently
                // working) so we delegate this to a different (non doctrine) subscriber.
                // Not 100% clear why we have to do that and if this grants us total safety with this)

                $this->dispatcher->dispatch(new SharedEntityEvent($entity), 'digital_ascetic.shared.entity.persist');
            }
        }
    }

    private function isSharedEntity($entity)
    {
        return ($entity instanceof SharedEntity);
    }

}
