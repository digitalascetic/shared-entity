<?php

namespace DigitalAscetic\SharedEntityBundle\EventListener;


use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use Symfony\Component\EventDispatcher\Event;

class SharedEntityEvent extends Event
{

    /** @var  SharedEntity */
    private $entity;

    /**
     * SharedEntityEvent constructor.
     * @param SharedEntity $entity
     */
    public function __construct(SharedEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return SharedEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
}