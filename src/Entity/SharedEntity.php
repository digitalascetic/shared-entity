<?php

namespace DigitalAscetic\SharedEntityBundle\Entity;

interface SharedEntity
{
    /**
     * @return Source|null
     */
    public function getSource(): ?Source;

    /**
     * @param Source $source
     */
    public function setSource(Source $source);


}
