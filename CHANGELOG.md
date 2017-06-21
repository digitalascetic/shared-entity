# CHANGELOG

1.0.6
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