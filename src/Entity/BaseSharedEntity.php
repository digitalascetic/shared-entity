<?php

namespace DigitalAscetic\SharedEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class BaseSharedEntity
 * @package DigitalAscetic\SharedEntityBundle\Entity
 */
abstract class BaseSharedEntity implements SharedEntity
{

    /**
     * @var int|null
     *
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    #[Groups('id')]
    protected ?int $id = null;

    /**
     * @var Source|null
     *
     */
    #[ORM\Embedded(class: 'DigitalAscetic\SharedEntityBundle\Entity\Source')]
    #[Groups('shared_entity')]
    protected ?Source $source = null;

    /**
     * BaseSharedEntity constructor.
     * @param Source|null $entitySource
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
     * @return Source|null
     */
    public function getSource(): ?Source
    {
        return $this->source;
    }

    /**
     * @param Source|null $source
     */
    public function setSource(?Source $source): void
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
