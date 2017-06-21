<?php

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use DigitalAscetic\SharedEntityBundle\Service\DoctrineSharedEntityConstructor;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM as ORM;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use Metadata\MetadataFactory;
use PhpCollection\Map;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class SharedEntityFunctionalTest extends KernelTestCase
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {

        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/DigitalAsceticSharedEntityBundle');

        self::bootKernel();

        $this->importDatabaseSchema();

        $this->em = static::$kernel->getContainer()
          ->get('doctrine')
          ->getManager();
    }

    /**
     * Persist local entity and see it assigned default local origin
     */
    public function testPersistLocalSharedEntity()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $this->assertInstanceOf(SharedEntity::class, $sharedEntity);
        $this->assertNotNull($sharedEntity->getId());
        $this->assertNotNull($sharedEntity->getSource());
        $this->assertEquals($sharedEntity->getId(), $sharedEntity->getSource()->getId());
        $this->assertEquals('test-origin', $sharedEntity->getSource()->getOrigin());
    }

    /**
     * Persist entity already with source
     */
    public function testPersistRemoteSharedEntity()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $this->assertInstanceOf(SharedEntity::class, $sharedEntity);
        $this->assertNotNull($sharedEntity->getId());
        $this->assertNotNull($sharedEntity->getSource());
        $this->assertEquals(12313, $sharedEntity->getSource()->getId());
        $this->assertEquals('remote-origin', $sharedEntity->getSource()->getOrigin());
    }

    public function testDeserializeRemoteSharedEntity()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $sharedEntity->setCode('local code');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $serializer = new Serializer(
          new MetadataFactory(new AnnotationDriver(new AnnotationReader())),
          new HandlerRegistry(),
          new DoctrineSharedEntityConstructor(
            static::$kernel->getContainer()->get('doctrine'),
            new UnserializeObjectConstructor(),
            static::$kernel->getContainer()->get('digital_ascetic.shared_entity_service'),
            static::$kernel->getContainer()->get('logger')
          ),
          new Map(array('json' => new JsonSerializationVisitor($namingStrategy))),
          new Map(array('json' => new JsonDeserializationVisitor($namingStrategy)))
        );

        $jsonSe = '{"id": "92090", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "12313"}}';

        /** @var TestSharedEntity $desSharedEntity */
        $desSharedEntity = $serializer->deserialize($jsonSe, TestSharedEntity::class, 'json');

        $this->assertNotNull($desSharedEntity);
        $this->assertInstanceOf(TestSharedEntity::class, $desSharedEntity);
        $this->assertEquals($sharedEntity->getId(), $desSharedEntity->getId());
        $this->assertEquals('remote-shared', $desSharedEntity->getName());
        $this->assertEquals('local code', $desSharedEntity->getCode());

    }

    public function testDeserializeComposedRemoteSharedEntity()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $sharedEntity->setCode('local code');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);

        $composedSharedEntity = new TestComposedSharedEntity('12345678');
        $composedSharedEntity->setSharedEntity($sharedEntity);
        $composedSource = new Source('remote-origin', '49858');
        $composedSharedEntity->setSource($composedSource);
        $this->em->persist($composedSharedEntity);

        $this->em->flush();

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $serializer = new Serializer(
          new MetadataFactory(new AnnotationDriver(new AnnotationReader())),
          new HandlerRegistry(),
          new DoctrineSharedEntityConstructor(
            static::$kernel->getContainer()->get('doctrine'),
            new UnserializeObjectConstructor(),
            static::$kernel->getContainer()->get('digital_ascetic.shared_entity_service'),
            static::$kernel->getContainer()->get('logger')
          ),
          new Map(array('json' => new JsonSerializationVisitor($namingStrategy))),
          new Map(array('json' => new JsonDeserializationVisitor($namingStrategy)))
        );

        $jsonSe = '{"id": "92090", "source": { "origin": "remote-origin", "id": "49858"}, "sharedEntity": {"id": "92090", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "12313"}}}';

        /** @var TestComposedSharedEntity $desComposed */
        $desComposed = $serializer->deserialize($jsonSe, TestComposedSharedEntity::class, 'json');

        $this->assertNotNull($desComposed);
        $this->assertInstanceOf(TestComposedSharedEntity::class, $desComposed);
        $this->assertEquals($composedSharedEntity->getId(), $desComposed->getId());
        $this->assertNotNull($desComposed->getSharedEntity());
        $this->assertInstanceOf(TestSharedEntity::class, $desComposed->getSharedEntity());
        $this->assertEquals($sharedEntity->getId(), $desComposed->getSharedEntity()->getId());
        $this->assertNotNull($desComposed->getSharedEntity()->getSource());
        $this->assertEquals('12313', $desComposed->getSharedEntity()->getSource()->getId());

    }

    public function testDeserializeLocalSharedEntityWithoutSource()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $sharedEntity->setCode('local code');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $serializer = new Serializer(
          new MetadataFactory(new AnnotationDriver(new AnnotationReader())),
          new HandlerRegistry(),
          new DoctrineSharedEntityConstructor(
            static::$kernel->getContainer()->get('doctrine'),
            new UnserializeObjectConstructor(),
            static::$kernel->getContainer()->get('digital_ascetic.shared_entity_service'),
            static::$kernel->getContainer()->get('logger')
          ),
          new Map(array('json' => new JsonSerializationVisitor($namingStrategy))),
          new Map(array('json' => new JsonDeserializationVisitor($namingStrategy)))
        );

        $id = $sharedEntity->getId();

        // Omit source in serialized version but use local correct id
        $jsonSe = '{"id": "'. $id .'", "name": "remote-shared"}';

        /** @var TestSharedEntity $desSharedEntity */
        $desSharedEntity = $serializer->deserialize($jsonSe, TestSharedEntity::class, 'json');

        $this->assertNotNull($desSharedEntity);
        $this->assertInstanceOf(TestSharedEntity::class, $desSharedEntity);
        $this->assertEquals($sharedEntity->getId(), $desSharedEntity->getId());
        $this->assertEquals('remote-shared', $desSharedEntity->getName());
        $this->assertEquals('local code', $desSharedEntity->getCode());

    }

    public function testDeserializeRemoteNonExistingSharedEntity()
    {

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $serializer = new Serializer(
          new MetadataFactory(new AnnotationDriver(new AnnotationReader())),
          new HandlerRegistry(),
          new DoctrineSharedEntityConstructor(
            static::$kernel->getContainer()->get('doctrine'),
            new UnserializeObjectConstructor(),
            static::$kernel->getContainer()->get('digital_ascetic.shared_entity_service'),
            static::$kernel->getContainer()->get('logger')
          ),
          new Map(array('json' => new JsonSerializationVisitor($namingStrategy))),
          new Map(array('json' => new JsonDeserializationVisitor($namingStrategy)))
        );

        $jsonSe = '{"id": "92090", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "12313"}}';

        /** @var TestSharedEntity $desSharedEntity */
        $desSharedEntity = $serializer->deserialize($jsonSe, TestSharedEntity::class, 'json');

        $this->assertNotNull($desSharedEntity);
        $this->assertInstanceOf(TestSharedEntity::class, $desSharedEntity);
        // id is not unserialized because the entity will get a new local id on persist
        // while maintaining the source
        $this->assertNull($desSharedEntity->getId());
        $this->assertEquals('remote-shared', $desSharedEntity->getName());

    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    protected function importDatabaseSchema()
    {
        $em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadata);
        }
    }

}
