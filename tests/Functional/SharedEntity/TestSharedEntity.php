<?php

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;

/**
 * Class TestSharedEntity
 * @package DigitalAscetic\SharedEntityBundle\Functional\SharedEntity
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class TestSharedEntity implements SharedEntity
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     * @Type("string")
     */
    private $code;

    /**
     * @var Source
     *
     * @ORM\Embedded(class = "DigitalAscetic\SharedEntityBundle\Entity\Source")
     * @Type("DigitalAscetic\SharedEntityBundle\Entity\Source")
     */
    private $source;

    /**
     * @var TestComposedSharedEntity
     *
     * @ORM\ManyToOne(targetEntity="DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestComposedSharedEntity")
     * @ORM\JoinColumn(name="composedSharedEntity_id", referencedColumnName="id", nullable=true)
     * @Type("DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestComposedSharedEntity")
     */
    private $composedEntity;

    /**
     * TestSharedEntity constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return TestComposedSharedEntity
     */
    public function getComposedEntity()
    {
        return $this->composedEntity;
    }

    /**
     * @param TestComposedSharedEntity $composedEntity
     */
    public function setComposedEntity($composedEntity)
    {
        $this->composedEntity = $composedEntity;
    }
    
}