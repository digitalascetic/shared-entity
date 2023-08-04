<?php
/**
 * Created by IntelliJ IDEA.
 * User: martino
 * Date: 14/03/17
 * Time: 14:00
 */

namespace DigitalAscetic\SharedEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class Source
 * @package DigitalAscetic\SharedEntityBundle\Entity\Base\SharedEntity
 *
 */
#[ORM\Embeddable]
class Source
{

    /**
     * Create a Source object from a string representing a uniqe id in the
     * form <origin>@<id>
     *
     * @param string $uniqeId
     * @return Source
     */
    public static function createSourceFromUniqueId($uniqeId)
    {
        if (strpos($uniqeId, '@')) {
            list($id, $origin) = explode('@', $uniqeId);

            return new Source($origin, $id);
        }

        return new Source(null, $uniqeId);
    }

    /**
     * @var string|null
     *
     */
    #[ORM\Column(name: 'origin', type: 'string', nullable: true)]
    #[Groups('shared_entity')]
    private ?string $origin = null;

    /**
     * @var string|null
     *
     */
    #[ORM\Column(name: 'id', type: 'string', nullable: true)]
    #[Groups('shared_entity')]
    private ?string $id = null;

    /**
     * Source constructor.
     * @param string|null $origin
     * @param string|null $id
     */
    public function __construct(?string $origin = null, mixed $id = null)
    {
        $this->origin = $origin;
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUniqueId()
    {
        return $this->id . '@' . $this->origin;
    }

    /**
     * Name used in the __toString method
     *
     * @return string
     */
    public function __toString()
    {
        return 'Source: ' . $this->origin . ' - ' . $this->id;
    }

}
