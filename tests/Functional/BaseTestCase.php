<?php

namespace DigitalAscetic\SharedEntityBundle\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseTestCase extends KernelTestCase
{
    protected static function createKernel(array $options = array())
    {
        return self::$kernel = new AppKernel(
          isset($options['config']) ? $options['config'] : 'config_test.yml'
        );
    }

    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/DigitalAsceticSharedEntityBundle/');
    }
}