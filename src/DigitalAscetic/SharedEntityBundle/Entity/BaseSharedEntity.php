<?php

namespace DigitalAscetic\SharedEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Groups;

/**
 * Class BaseSharedEntity
 * @package DigitalAscetic\SharedEntityBundle\Entity
 */
abstract class BaseSharedEntity implements SharedEntity
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({"id"})
     * @Type("integer")
     */
    protected $id;

    /**
     * @var Source
     *
     * @ORM\Embedded(class = "DigitalAscetic\SharedEntityBundle\Entity\Source")
     * @Type("DigitalAscetic\SharedEntityBundle\Entity\Source")
     * @Groups({"shared_entity"})
     */
    protected $source;

    /**
     * BaseSharedEntity constructor.
     * @param Source $entitySource
     */
    public function __construct(Source $entitySource = null)
    {
        $this->source = $entitySource;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Source $source
     */
    public function setSource(Source $source)
    {
        $this->source = $source;
    }

    /**
     * Returns true if the passed entity is the same shared entity of the
     * current one.
     *
     * @param SharedEntity $entity
     * @return bool
     */
    public function isSameSharedEntity(SharedEntity $entity)
    {
        if ($entity && $entity->getSource()->getUniqueId() == $this->getSource()->getUniqueId()) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the object has the same source of the current one.
     *
     * @param SharedEntity $entity
     * @return bool
     */
    public function hasSameOrigin(SharedEntity $entity)
    {
        if ($entity && $entity->getSource() && $this->getSource() &&
          $entity->getSource()->getOrigin() == $this->getSource()->getOrigin()
        ) {
            return true;
        }

        return false;
    }

}