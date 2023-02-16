<?php

namespace DigitalAscetic\SharedEntityBundle\Serializer\Normalizer;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use DigitalAscetic\SharedEntityBundle\Service\SharedEntityService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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

            if (is_array($data)) {
                // Always avoid to deserialize the id to avoid update clashes, could be a remote id, just trust source
                unset($data['id']);

                // origin might be absent for globally shared entities
                $origin = $data['source']['origin'] ?? null;

                $source = new Source($origin, $data['source']['id']);
            } else {
                // Always avoid to deserialize the id to avoid update clashes, could be a remote id, just trust source
                unset($data->{'id'});

                /** @var SharedEntity $data */
                $source = $data->getSource();
            }

            // See if the shared entity is already in local db or in cache
            $object = $this->getEntityFromSource($type, $source);

            // If an actual entity could be found initialize and return it
            if ($object) {
                $this->logger->info('Updating existing shared entity with source ' . $object->getSource());

                $this->setConstructorArguments($object, $data);

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

            return $object;
        } else if (is_array($data) && array_key_exists('id', $data) && $data['id']) {
            $object = $this->registry->getRepository($type)->find($data['id']);

            if ($object) {

                $this->setConstructorArguments($object, $data);

                $context = array_merge(
                    $context,
                    [
                        AbstractNormalizer::OBJECT_TO_POPULATE => $object
                    ]
                );
            }
        } else if (is_object($data) && property_exists($data, 'id')) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $id = $accessor->getValue($data, 'id');

            if ($id) {
                $object = $this->registry->getRepository($type)->find($id);

                if ($object) {

                    $this->setConstructorArguments($object, $data);

                    $context = array_merge(
                        $context,
                        [
                            AbstractNormalizer::OBJECT_TO_POPULATE => $object
                        ]
                    );
                }
            }
        }

        return $this->normalizer->denormalize($data, $type, $format, $context);
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

    /**
     * In order to avoid MissingConstructorArgumentsException we need to set constructor arguments if are missing
     *
     * https://symfony.com/doc/current/components/serializer.html#handling-constructor-arguments
     *
     * @param mixed $object
     * @param $data
     * @return void
     * @throws \ReflectionException
     */
    private function setConstructorArguments(mixed $object, &$data)
    {
        if (is_object($object)) {
            $refClass = new \ReflectionClass($object);
            $constructorArguments = $refClass->getConstructor()->getParameters();
            $accessor = PropertyAccess::createPropertyAccessor();

            foreach ($constructorArguments as $argument) {
                if (!$argument->allowsNull() && !array_key_exists($argument->getName(), $data)) {
                    $data[$argument->getName()] = $accessor->getValue($object, $argument->getName());
                }
            }
        }
    }

    private function hasSourceData($data): bool
    {
        if (is_array($data)) {
            if (isset($data['source']) && isset($data['source']['id'])) {
                return true;
            }
        } else if (is_object($data) && property_exists($data, 'source')) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $source = $accessor->getValue($data, 'source');

            if ($source instanceof Source) {
                if ($source->getOrigin() && $source->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        if (str_ends_with($type, '[]')) {
            return false;
        }

        return in_array(SharedEntity::class, class_implements($type));
    }
}
