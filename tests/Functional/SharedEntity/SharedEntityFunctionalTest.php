<?php

namespace DigitalAscetic\SharedEntityBundle\Test\Functional\SharedEntity;

use DigitalAscetic\SharedEntityBundle\Entity\SharedEntity;
use DigitalAscetic\SharedEntityBundle\Entity\Source;
use DigitalAscetic\SharedEntityBundle\Service\SharedEntityService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SharedEntityFunctionalTest extends KernelTestCase
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {

        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir() . '/DigitalAsceticSharedEntityBundle');

        self::bootKernel();

        $this->importDatabaseSchema();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Persist local entity, entity is persisted and source is set to default origin/local id
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
     * Persist local entity, entity is persisted and source is set to default origin/local id
     */
    public function testPersistLocalSharedEntityWithTrait()
    {
        $sharedEntity = new TestTraitSharedEntity('shared-trait');
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $this->assertInstanceOf(SharedEntity::class, $sharedEntity);
        $this->assertNotNull($sharedEntity->getId());
        $this->assertNotNull($sharedEntity->getSource());
        $this->assertEquals($sharedEntity->getId(), $sharedEntity->getSource()->getId());
        $this->assertEquals('test-origin', $sharedEntity->getSource()->getOrigin());
    }

    /**
     * Persist entity already with source, entity is persisted but source is left untouched
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

    /**
     * Test entity retrieval by source
     */
    public function testRetrieveEntityFromSource()
    {

        $remoteSE = new TestSharedEntity('remote');
        $source = new Source('remote-origin', '12313');
        $remoteSE->setSource($source);

        $localSE = new TestSharedEntity('edu');

        $globalSE = new TestSharedEntity('global');
        $sourceGlobal = new Source(null, '999');
        $globalSE->setSource($sourceGlobal);


        $this->em->persist($remoteSE);
        $this->em->persist($localSE);
        $this->em->persist($globalSE);

        $this->em->flush();

        $remoteId = $remoteSE->getId();
        $localId = $localSE->getId();

        $globalId = $globalSE->getId();

        $this->em->clear();

        /** @var SharedEntityService $seService */
        $seService = static::$kernel->getContainer()->get('digital_ascetic.shared_entity_service');

        $remoteFromSource = $seService->getEntityFromSource(TestSharedEntity::class,
            new Source('remote-origin', '12313'));
        $localFromSource = $seService->getEntityFromSource(TestSharedEntity::class,
            new Source($seService->getOrigin(), $localId));
        $globalFromSource = $seService->getEntityFromSource(TestSharedEntity::class,
            new Source(null, '999'));


        $entities = $this->em->getRepository(TestSharedEntity::class)->findAll();

        foreach ($entities as $entity) {
            var_dump($entity);
        }

        $this->assertNotNull($remoteFromSource);
        $this->assertNotNull($localFromSource);
        $this->assertNotNull($globalFromSource);
        $this->assertNotNull($remoteFromSource->getId());
        $this->assertNotNull($localFromSource->getId());
        $this->assertNotNull($globalFromSource->getId());
        $this->assertEquals($remoteId, $remoteFromSource->getId());
        $this->assertEquals($localId, $localFromSource->getId());
        $this->assertEquals($globalId, $globalFromSource->getId());

    }

    /**
     * Deserialize an already locally persisted remote entity,
     * remote entity is identified through source and override local
     * persisted entity.
     */
    public function testDeserializeRemoteSharedEntity()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $sharedEntity->setCode('local code');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $id = $sharedEntity->getId();

        $this->em->clear();

        $serializer = $this->getSerializer();

        $jsonSe = '{"id": "92090", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "12313"}}';

        /** @var TestSharedEntity $desSharedEntity */
        $desSharedEntity = $serializer->deserialize($jsonSe, TestSharedEntity::class, 'json');

        $this->assertNotNull($desSharedEntity);
        $this->assertInstanceOf(TestSharedEntity::class, $desSharedEntity);
        $this->assertEquals($id, $desSharedEntity->getId());
        $this->assertEquals('remote-shared', $desSharedEntity->getName());
        $this->assertEquals('local code', $desSharedEntity->getCode());

    }

    /**
     * Deserialize an already locally persisted remote entity,
     * remote entity is identified through source and override local
     * persisted entity.
     */
    public function testDeserializeRemoteSharedEntityWithTrait()
    {
        $sharedEntity = new TestTraitSharedEntity('shared');
        $sharedEntity->setCode('local code');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);
        $this->em->flush();

        $id = $sharedEntity->getId();

        $this->em->clear();

        $serializer = $this->getSerializer();

        $jsonSe = '{"id": "92090", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "12313"}}';

        /** @var TestSharedEntity $desSharedEntity */
        $desSharedEntity = $serializer->deserialize($jsonSe, TestTraitSharedEntity::class, 'json');

        $this->assertNotNull($desSharedEntity);
        $this->assertInstanceOf(TestTraitSharedEntity::class, $desSharedEntity);
        $this->assertEquals($id, $desSharedEntity->getId());
        $this->assertEquals('remote-shared', $desSharedEntity->getName());
        $this->assertEquals('local code', $desSharedEntity->getCode());

    }

    /**
     * Deserialize an already locally persisted remote entity associated to another shared entity
     * remote entity is identified through source and override local
     * persisted entity.
     */
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

        $this->em->detach($sharedEntity);
        $this->em->detach($composedSharedEntity);

        $serializer = $this->getSerializer();

        $jsonSe = '{"id": "49858", "source": { "origin": "remote-origin", "id": "49858"}, "sharedEntity": {"id": "12313", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "12313"}}}';

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
        $this->assertEquals('remote-shared', $desComposed->getSharedEntity()->getName());

    }

    /**
     * Deserialize an already locally persisted remote entity associated to another non persisted
     * shared entity remote entity is identified through source and override local
     * persisted entity.
     */
    public function testDeserializeComposedRemotePersistedSharedEntity()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $sharedEntity->setCode('local code');
        $source = new Source('remote-origin', '99');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);

        $sharedEntity2 = new TestSharedEntity('shared');
        $sharedEntity2->setCode('local code 2');
        $source = new Source('remote-origin', '100');
        $sharedEntity2->setSource($source);
        $this->em->persist($sharedEntity2);

        $composedSharedEntity = new TestComposedSharedEntity('12345678');
        $composedSharedEntity->setSharedEntity($sharedEntity);
        $composedSource = new Source('remote-origin', '88');
        $composedSharedEntity->setSource($composedSource);

        $this->em->persist($composedSharedEntity);
        $this->em->persist($sharedEntity2);

        $this->em->flush();

        $this->em->detach($sharedEntity);
        $this->em->detach($sharedEntity2);
        $this->em->detach($composedSharedEntity);

        $this->assertEquals($sharedEntity->getId(), $composedSharedEntity->getSharedEntity()->getId());

        $serializer = $this->getSerializer();

        $jsonSe = '{"id": "49858", "source": { "origin": "remote-origin", "id": "88"}, "sharedEntity": {"id": "11111", "name": "remote-shared", "source": { "origin": "remote-origin", "id": "100"}}}';

        /** @var TestComposedSharedEntity $desComposed */
        $desComposed = $serializer->deserialize($jsonSe, TestComposedSharedEntity::class, 'json');

        $this->assertNotNull($desComposed);
        $this->assertInstanceOf(TestComposedSharedEntity::class, $desComposed);
        $this->assertEquals($composedSharedEntity->getId(), $desComposed->getId());
        $this->assertNotNull($desComposed->getSharedEntity());
        $this->assertInstanceOf(TestSharedEntity::class, $desComposed->getSharedEntity());
        $this->assertNotNull($desComposed->getSharedEntity()->getSource());
        $this->assertEquals($sharedEntity2->getSource()->getId(), $desComposed->getSharedEntity()->getSource()->getId());
        $this->assertEquals($sharedEntity2->getId(), $desComposed->getSharedEntity()->getId());
    }

    /**
     *  Deserialize non SharedEntity will have same
     * result as DoctrineObjectConstructor
     */
    public function testDeserializeLocalSharedEntityWithoutSource()
    {
        $sharedEntity = new TestSharedEntity('shared');
        $sharedEntity->setCode('code');
        $source = new Source('remote-origin', '12313');
        $sharedEntity->setSource($source);
        $this->em->persist($sharedEntity);
        $this->em->flush();
        $this->em->detach($sharedEntity);

        $serializer = $this->getSerializer();

        $id = $sharedEntity->getId();

        // Omit source in serialized version but use local correct id
        $jsonSe = '{"id": ' . $id . ', "name": "remote-shared"}';

        /** @var TestSharedEntity $desSharedEntity */
        $desSharedEntity = $serializer->deserialize($jsonSe, TestSharedEntity::class, 'json');

        $this->assertNotNull($desSharedEntity);
        $this->assertInstanceOf(TestSharedEntity::class, $desSharedEntity);
        $this->assertEquals($sharedEntity->getId(), $desSharedEntity->getId());
        $this->assertEquals('remote-shared', $desSharedEntity->getName());
        $this->assertEquals('code', $desSharedEntity->getCode());

    }

    /**
     * Deserialize a non existing remote shared entity
     */
    public function testDeserializeRemoteNonExistingSharedEntity()
    {

        $serializer = $this->getSerializer();

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
     * Deserialize a locally persisted local shared entity
     */
    public function testDeserializeLocalSharedEntity()
    {

        $localSharedEntity = new TestSharedEntity('default shared');
        $this->em->persist($localSharedEntity);
        $this->em->flush();
        $persistedId = $localSharedEntity->getId();
        $this->em->detach($localSharedEntity);

        $localSerialized = '{"id": "999999", "name": "remote-shared", "source": { "origin": "' . $this->getLocalOrigin() . '", "id": "' . $persistedId . '"}}';

        $serializer = $this->getSerializer();

        /** @var TestSharedEntity $remoteSE */
        $localSE = $serializer->deserialize($localSerialized, TestSharedEntity::class, 'json');

        $this->assertNotNull($localSE);
        $this->assertEquals($persistedId, $localSE->getId());
        $this->assertEquals('remote-shared', $localSE->getName());
        $this->assertNotNull($localSE->getSource());
        $this->assertEquals($persistedId, $localSE->getSource()->getId());
        $this->assertEquals('test-origin', $localSE->getSource()->getOrigin());

    }

    /**
     * Deserialize remote non locally persisted entity with local locally persisted entity
     */
    public function testDeserializeSharedEntityWithAssociatedShareEntityAndDifferentOrigins()
    {

        $localSharedEntity = new TestSharedEntity('default shared');
        $this->em->persist($localSharedEntity);
        $this->em->flush();
        $persistedId = $localSharedEntity->getId();
        $this->em->detach($localSharedEntity);

        $serializer = $this->getSerializer();

        $serializedComposedSE = '{"id": "49858", "source": { "origin": "remote-origin", "id": "49858"}, "sharedEntity": {"id": "999999", "name": "local shared", "source": { "origin": "' . $this->getLocalOrigin() . '", "id": "' . $persistedId . '"}}}';

        /** @var TestComposedSharedEntity $composedSE */
        $composedSE = $serializer->deserialize($serializedComposedSE, TestComposedSharedEntity::class, 'json');

        $this->assertNotNull($composedSE);
        $this->assertInstanceOf(TestComposedSharedEntity::class, $composedSE);
        $this->assertNull($composedSE->getId());
        $this->assertNotNull($composedSE->getSource());
        $this->assertEquals('49858', $composedSE->getSource()->getId());
        $this->assertEquals('remote-origin', $composedSE->getSource()->getOrigin());
        $this->assertNotNull($composedSE->getSharedEntity());
        $this->assertInstanceOf(TestSharedEntity::class, $composedSE->getSharedEntity());
        $this->assertNotNull($composedSE->getSharedEntity()->getId());
        $this->assertEquals($persistedId, $composedSE->getSharedEntity()->getId());
        $this->assertEquals('local shared', $composedSE->getSharedEntity()->getName());

    }

    public function testDeserializeSharedEntityWithoutConstructorArguments()
    {
        $localSharedEntity = new TestSharedEntity('default shared constructor');
        $this->em->persist($localSharedEntity);
        $this->em->flush();
        $persistedId = $localSharedEntity->getId();
        $this->em->detach($localSharedEntity);

        $serializer = $this->getSerializer();

        $jsonSe = '{"phone": "222333999", "sharedEntity": {"id": "' . $persistedId . '", "code" : "constructor"}}';

        /** @var TestComposedSharedEntity $desComposed */
        $desComposed = $serializer->deserialize($jsonSe, TestComposedSharedEntity::class, 'json', [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]);
        $this->assertNotNull($desComposed);
        $this->assertInstanceOf(TestComposedSharedEntity::class, $desComposed);
        $this->assertEquals($persistedId, $desComposed->getSharedEntity()->getId());
        $this->assertEquals('default shared constructor', $desComposed->getSharedEntity()->getName());
    }

    /**
     * Deserialize a non already persisted entity with a circular reference (use cache)
     */
    public function testDeserializeComposedRemoteNewSharedEntity()
    {

        $serializer = $this->getSerializer();

        $jsonSe = '{"id": "251282", "source": { "origin": "remote-origin", "id": "251282"}, "sharedEntity": {"id": "11111", "name": "test", "source": { "origin": "remote-origin", "id": "11111"}, "composedEntity": {"id": "251282", "source": { "origin": "remote-origin", "id": "251282"}}}}';

        /** @var TestComposedSharedEntity $desComposed */
        $desComposed = $serializer->deserialize($jsonSe, TestComposedSharedEntity::class, 'json');

        $this->assertNotNull($desComposed);
        $this->assertInstanceOf(TestComposedSharedEntity::class, $desComposed);
        $this->assertNotNull($desComposed->getSharedEntity());
        $this->assertInstanceOf(TestSharedEntity::class, $desComposed->getSharedEntity());
        $this->assertNotNull($desComposed->getSharedEntity()->getSource());
        $this->assertEquals('11111', $desComposed->getSharedEntity()->getSource()->getId());
        $this->assertNotNull($desComposed->getSharedEntity()->getComposedEntity());
        $this->assertInstanceOf(TestComposedSharedEntity::class, $desComposed->getSharedEntity()->getComposedEntity());

        /**
         * @todo We can control object instantation so our cache is never called
         */
        $this->assertSame($desComposed, $desComposed->getSharedEntity()->getComposedEntity());

    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
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

    private function getSerializer(): Serializer
    {

        $container = static::getContainer();

        /** @var Serializer $serializer */
        $serializer = $container->get('serializer');

        return $serializer;

    }

    private function getLocalOrigin()
    {
        return "test-origin";
    }

}
