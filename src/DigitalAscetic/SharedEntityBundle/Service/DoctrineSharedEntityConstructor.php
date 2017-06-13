<?php

namespace DigitalAscetic\SharedEntityBundle\Service;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DoctrineSharedEntityConstructor
 * @package DigitalAscetic\SharedEntityBundle\Service
 */
class DoctrineSharedEntityConstructor implements ObjectConstructorInterface
{

    /** @var SharedEntityService */
    private $sharedEntityService;

    /** @var ManagerRegistry */
    private $managerRegistry;

    /** @var ObjectConstructorInterface */
    private $fallbackConstructor;

    /** @var  LoggerInterface */
    private $logger;

    /**
     * DoctrineSharedEntityConstructor constructor.
     * @param ManagerRegistry $managerRegistry
     * @param ObjectConstructorInterface $fallbackConstructor
     * @param SharedEntityService $sharedEntityService
     * @param LoggerInterface $logger
     */
    public function __construct(
      ManagerRegistry $managerRegistry,
      ObjectConstructorInterface $fallbackConstructor,
      SharedEntityService $sharedEntityService,
      LoggerInterface $logger
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->fallbackConstructor = $fallbackConstructor;
        $this->sharedEntityService = $sharedEntityService;
        $this->logger = $logger;

    }


    /**
     * {@inheritdoc}
     */
    public function construct(
      VisitorInterface $visitor,
      ClassMetadata $metadata,
      $data,
      array $type,
      DeserializationContext $context
    ) {

        // Locate possible ObjectManager
        $objectManager = $this->managerRegistry->getManagerForClass($metadata->name);

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // Locate possible ClassMetadata
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($metadata->name)) {
            // No ClassMetadata found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // If it's a managed class and also a SharedEntity follow a special object construction flow
        if ($metadata->reflection->implementsInterface(SharedEntity::class)) {

            // Just handle entities having source data and coming from different origins (otherwise we already have the id)
            if ($this->hasSourceData($data)) {

                // origin might be absent for globally shared entities
                $origin = isset($data['source']['origin']) ? $data['source']['origin'] : null;

                // See if the shared entity is already in local db
                $object = $this->sharedEntityService->getEntityFromSource(
                  $metadata->name,
                  new Source($origin, $data['source']['id'])
                );

                // If an actual entity could be found initialize and return it
                if ($object) {

                    $this->logger->info('Updating existing shared entity with source '.$object->getSource());
                    $objectManager->initializeObject($object);

                    return $object;
                }

            }

        }

        // Managed entity, check for proxy load
        if (!is_array($data)) {
            // Single identifier, load proxy
            return $objectManager->getReference($metadata->name, $data);
        }

        // Fallback to default constructor if missing identifier(s)
        $classMetadata = $objectManager->getClassMetadata($metadata->name);
        $identifierList = array();

        foreach ($classMetadata->getIdentifierFieldNames() as $name) {
            if (!array_key_exists($name, $data)) {
                return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
            }

            $identifierList[$name] = $data[$name];
        }

        // Entity update, load it from database
        $object = $objectManager->find($metadata->name, $identifierList);

        if (!$object) {
            throw new RuntimeException(
              "Cannot find an entity of type ".$type['name'].' with identifiers: ['.join(
                ',',
                $identifierList
              ).']. Possibly sharing entity that do not implements SharedEntity interface or missing source infos.'
            );
        }

        $objectManager->initializeObject($object);

        return $object;
    }

    private function hasSourceData($data)
    {

        if (!isset($data['source']) || !isset($data['source']['id'])) {
            return false;
        }

        if (isset($data['source']['origin']) && $data['source']['origin'] === $this->sharedEntityService->getOrigin()) {
            return false;
        }

        return true;

    }
}