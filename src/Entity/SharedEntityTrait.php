<?php
/**
 * Created by IntelliJ IDEA.
 * User: martino
 * Date: 19/11/17
 * Time: 12:31
 */

namespace DigitalAscetic\SharedEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait SharedEntityTrait
{

    /**
     * @var Source
     *
     */
    #[ORM\Embedded(class: 'DigitalAscetic\SharedEntityBundle\Entity\Source')]
    #[Groups('shared_entity')]
    protected ?Source $source = null;

    /**
     * @return Source|null
     */
    public function getSource(): ?Source
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
