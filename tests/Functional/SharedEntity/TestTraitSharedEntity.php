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
use JMS\Serializer\Annotation\Type;

/**
 * Class TestTraitSharedEntity
 * @package Functional\SharedEntity
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class TestTraitSharedEntity implements SharedEntity
{
    use SharedEntityTrait;

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
     * TestTraitSharedEntity constructor.
     * @param string $name
     */
    public function __construct($name)
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