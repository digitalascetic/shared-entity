# CHANGELOG

6.2.4
-----
* Handle object at SharedEntityDenormalizer

6.2.3
-----
* Avoid error with ArrayDenormalizer

6.2.2
-----
* Fixed SharedEntityDenormalizer MissingConstructorArgumentsException

6.2.1
-----
* Fixed SharedEntityDenormalizer return denormalize data.

6.2.0
-----
* Symfony 6.2 compatibility.

3.0.0
-----
* Added DoctrineSharedEntityPass (CompilerPass) to decorate jms_serializer.object_constructor.
* Removed alias for jms_serializer.object_constructor.

2.0.1
------
* Fixed DoctrineSharedEntityConstructor $metadata->reflection is no longer available, instead we use \ReflectionClass

2.0.0
------
* Allow compatibility with JMS/Serializer to v.3.3.0
* Fixed DoctrineSharedEntityConstructor implementing ObjectConstructorInterface

1.0.12
------

 * Create ```SharedEntityTrait``` and tests.


1.0.11
------

 * **BUGFIX** Correctly handle deserializing non persisted entities with circular cross/circular references.


1.0.10
------

 * Added a static method to create ```Source``` object from source unique id.

1.0.9
------

 * **BUGFIX** Previous bugfix was not taking into consideration local/locally persisted entities: always use source id instead of deserialized id with ```SharedEntity```

1.0.8
-----

 * **BUGFIX** Always avoid deserializing remote id (id clash on updates). Also fixed error in regression test that allowed the bug to appear in the first place.

1.0.7
-----

 * **BUGFIX** Correctly handle non locally persisted remote ```SharedEntity``` (bug introduced in 1.0.6)
  

1.0.6
-----

 * Correctly handle serialized ```SharedEntity``` without source but with id (e.g. for client/server communication)
  

1.0.5
-----
 
 * Handle deserialization of "globally shared" ```SharedEntity``` represented by a ```null``` origin.
 * Added serialization group to ```Source``` properties to allow selective (de)serialization

1.0.4
-----

 * Handled case where an entity is shared without ```Source``` but with an id by throwing proper exception


1.0.1
-----

 * Feature: adding defuault id to ```BaseSharedEntity``` 
 * **Bugfix**: configuration parameter is ```origin``` and not ```default_origin```

1.0.0
-----

 * Initial release
