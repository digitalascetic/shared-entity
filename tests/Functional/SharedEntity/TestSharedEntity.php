<?php

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TestSharedEntity
 * @package DigitalAscetic\SharedEntityBundle\Functional\SharedEntity
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
#[ORM\Table]
#[ORM\Entity]
class TestSharedEntity implements SharedEntity
{

    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    private ?string $name = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'code', type: 'string', nullable: true)]
    private ?string $code = null;

    /**
     * @var Source|null
     *
     * @ORM\Embedded(class = "DigitalAscetic\SharedEntityBundle\Entity\Source")
     */
    #[ORM\Embedded(class: 'DigitalAscetic\SharedEntityBundle\Entity\Source')]
    private ?Source $source = null;

    /**
     * @var TestComposedSharedEntity|null
     *
     * @ORM\ManyToOne(targetEntity="DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestComposedSharedEntity")
     * @ORM\JoinColumn(name="composedSharedEntity_id", referencedColumnName="id", nullable=true)
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestComposedSharedEntity')]
    #[ORM\JoinColumn(name: 'composedSharedEntity_id', referencedColumnName: 'id', nullable: true)]
    private ?TestComposedSharedEntity $composedEntity = null;

    /**
     * TestSharedEntity constructor.
     * @param string|null $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

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
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
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
