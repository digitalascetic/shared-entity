<?php

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;


use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TestComposedSharedEntity
 * @package DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity
 *
 */
#[ORM\Table]
#[ORM\Entity]
class TestComposedSharedEntity implements SharedEntity
{

    /**
     * @var int|null
     *
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

    /**
     * @var string|null
     *
     */
    #[ORM\Column(name: 'phone', type: 'string', nullable: true)]
    private ?string $phone = null;

    /**
     * @var TestSharedEntity
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity\TestSharedEntity')]
    #[ORM\JoinColumn(name: 'sharedEntity_id', referencedColumnName: 'id', nullable: false)]
    private TestSharedEntity $sharedEntity;

    /**
     * @var Source
     *
     */
    #[ORM\Embedded(class: 'DigitalAscetic\SharedEntityBundle\Entity\Source')]
    private ?Source $source = null;

    /**
     * TestComposedSharedEntity constructor.
     * @param string|null $phone
     */
    public function __construct(?string $phone)
    {
        $this->phone = $phone;
    }

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
    public function getSharedEntity(): TestSharedEntity
    {
        return $this->sharedEntity;
    }

    /**
     * @param TestSharedEntity $sharedEntity
     */
    public function setSharedEntity(TestSharedEntity $sharedEntity): void
    {
        $this->sharedEntity = $sharedEntity;
    }

}
