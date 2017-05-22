<?php

namespace DigitalAscetic\SharedEntityBundle\Service;


use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;

/**
 * Class SharedEntityService
 * @package DigitalAscetic\SharedEntityBundle\Service
 */
class SharedEntityService
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $origin;

    /**
     * SharedEntityService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, $origin)
    {
        $this->em = $em;
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param $entityName
     * @param Source $source
     * @return SharedEntity
     */
    public function getEntityFromSource($entityName, Source $source)
    {
        $arr = $this->em->getRepository($entityName)->findBy(
          array('source.origin' => $source->getOrigin(), 'source.id' => $source->getId())
        );

        return empty($arr) ? null : $arr[0];
    }

    /**
     * @param $entityName
     * @param Source $source
     * @return SharedEntity
     */
    public function getEntityIdFromSource($entityName, Source $source)
    {
        $qb = $this->em->getRepository($entityName)->createQueryBuilder('s');
        $qb->select('s.id');
        $qb->where('s.source.origin = :origin');
        $qb->andWhere('s.source.id = :id');
        $qb->setParameter('origin', $source->getOrigin());
        $qb->setParameter('id', $source->getId());

        return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * @param $entityName
     * @param Source $source
     * @return SharedEntity
     */
    public function getReferenceToEntityFromSource($entityName, Source $source)
    {
        $id = $this->getEntityIdFromSource($entityName, $source);

        if (!$id) {
            return null;
        }

        return $this->em->getReference($entityName, $id);
    }

}