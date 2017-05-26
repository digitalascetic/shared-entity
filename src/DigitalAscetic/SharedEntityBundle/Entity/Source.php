<?php
/**
 * Created by IntelliJ IDEA.
 * User: martino
 * Date: 14/03/17
 * Time: 14:00
 */

namespace DigitalAscetic\SharedEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Source
 * @package DigitalAscetic\SharedEntityBundle\Entity\Base\SharedEntity
 *
 * @ORM\Embeddable
 */
class Source
{
    /**
     * @var string
     *
     * @ORM\Column(name="origin", type="string", length=255, nullable=true)
     * @Type("string")
     * @Groups({"shared_entity"})
     */
    private $origin;

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=255, nullable=true)
     * @Type("string")
     * @Groups({"shared_entity"})
     */
    private $id;

    /**
     * Source constructor.
     * @param string $origin
     * @param string $id
     */
    public function __construct($origin, $id)
    {
        $this->origin = $origin;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUniqueId()
    {
        return $this->id.'@'.$this->origin;
    }

    /**
     * Name used in the __toString method
     *
     * @return string
     */
    public function __toString()
    {
        return 'Source: '.$this->origin.' - '.$this->id;
    }

}