<?php
/**
 * Created by IntelliJ IDEA.
 * User: martino
 * Date: 19/11/17
 * Time: 12:34
 */

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;


use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\SharedEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TestTraitSharedEntity
 * @package Functional\SharedEntity
 *
 */
#[ORM\Table]
#[ORM\Entity]
class TestTraitSharedEntity implements SharedEntity
{
    use SharedEntityTrait;

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

    /**
     * @var string|null
     *
     */
    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    private ?string $name = null;

    /**
     * @var string|null
     *
     */
    #[ORM\Column(name: 'code', type: 'string', nullable: true)]
    private ?string $code = null;

    /**
     * TestTraitSharedEntity constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

}
