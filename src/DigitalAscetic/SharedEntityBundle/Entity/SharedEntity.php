<?php

namespace DigitalAscetic\SharedEntityBundle\Entity;

interface SharedEntity
{
    /**
     * @return Source
     */
    public function getSource();

    /**
     * @param Source $source
     */
    public function setSource(Source $source);


}