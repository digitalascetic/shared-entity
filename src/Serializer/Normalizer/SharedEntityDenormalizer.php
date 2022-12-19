<?php

namespace DigitalAscetic\SharedEntityBundle\Serializer\Normalizer;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use DigitalAscetic\SharedEntityBundle\Service\SharedEntityService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SharedEntityDenormalizer implements DenormalizerInterface
{
    private array $cache = [];

    /**
     * @param SharedEntityService $sharedEntityService
     * @param ObjectNormalizer $normalizer
     * @param LoggerInterface $logger
     */
    public function __construct(private SharedEntityService $sharedEntityService,
                                private ObjectNormalizer    $normalizer,
                                private ManagerRegistry     $registry,
                                private LoggerInterface     $logger)
    {

    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        $context = array_merge(
            [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return $object->getId();
                },
            ],
            $context
        );

        // Just handle entities having source data
        if ($this->hasSourceData($data)) {

            // Always avoid to deserialize the id to avoid update clashes, could be a remote id, just trust source
            unset($data['id']);

            // origin might be absent for globally shared entities
            $origin = $data['source']['origin'] ?? null;

            $source = new Source($origin, $data['source']['id']);

            // See if the shared entity is already in local db or in cache
            $object = $this->getEntityFromSource($type, $source);

            // If an actual entity could be found initialize and return it
            if ($object) {
                $this->logger->info('Updating existing shared entity with source ' . $object->getSource());

                $context = array_merge(
                    [
                        AbstractNormalizer::OBJECT_TO_POPULATE => $object
                    ],
                    $context
                );
            }

            if (array_key_exists($type . $source->getUniqueId(), $this->cache)) {
                return $object;
            }

            $object = $this->normalizer->denormalize($data, $type, $format, $context);

            if ($object && !array_key_exists($type . $source->getUniqueId(), $this->cache)) {
                $this->cache[$type . $source->getUniqueId()] = $object;
            }

        } else if (array_key_exists('id', $data) && $data['id']) {
            $object = $this->registry->getRepository($type)->find($data['id']);

            if ($object) {
                $context = array_merge(
                    [
                        AbstractNormalizer::OBJECT_TO_POPULATE => $object,
                        AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true
                    ],
                    $context
                );
            }

            $object = $this->normalizer->denormalize($data, $type, $format, $context);
        }

        return $object;
    }

    private function getEntityFromSource($entityName, Source $source)
    {
        $object = $this->sharedEntityService->getEntityFromSource(
            $entityName,
            $source
        );

        if (!$object && array_key_exists($entityName . $source->getUniqueId(), $this->cache)) {
            $object = $this->cache[$entityName . $source->getUniqueId()];
        }

        return $object;
    }

    private function hasSourceData($data): bool
    {
        if (!isset($data['source']) || !isset($data['source']['id'])) {
            return false;
        }

        return true;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return in_array(SharedEntity::class, class_implements($type));
    }
}
