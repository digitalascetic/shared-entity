<?php

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;


use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;

/**
 * Class TestComposedSharedEntity
 * @package DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class TestComposedSharedEntity implements SharedEntity
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Type("string")
     */
    private $phone;

    /**
     * @var TestSharedEntity
     *
     * @ORM\ManyToOne(targetEntity="DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestSharedEntity")
     * @ORM\JoinColumn(name="sharedEntity_id", referencedColumnName="id", nullable=false)
     * @Type("DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestSharedEntity")
     */
    private $sharedEntity;

    /**
     * @var Source
     *
     * @ORM\Embedded(class = "DigitalAscetic\SharedEntityBundle\Entity\Source")
     * @Type("DigitalAscetic\SharedEntityBundle\Entity\Source")
     */
    private $source;

    /**
     * TestComposedSharedEntity constructor.
     * @param string $phone
     */
    public function __construct($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Name used in the __toString method
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isPersisted()
    {
    }

    public function isSameEntity(Entity $entity)
    {
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
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return TestSharedEntity
     */
    public function getSharedEntity()
    {
        return $this->sharedEntity;
    }

    /**
     * @param TestSharedEntity $sharedEntity
     */
    public function setSharedEntity($sharedEntity)
    {
        $this->sharedEntity = $sharedEntity;
    }

}