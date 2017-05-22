<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    private $config;

    public function __construct($config)
    {
        parent::__construct('test', true);
        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__.'/Resources/config/config_'.$config.'.yml';
        }
        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }
        $this->config = $config;
    }

    public function registerBundles()
    {
        return array(
          new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
          new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
          new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
          new \DigitalAscetic\SharedEntityBundle\DigitalAsceticSharedEntityBundle(),
          new JMS\SerializerBundle\JMSSerializerBundle(),
          new \Symfony\Bundle\MonologBundle\MonologBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/DigitalAsceticSharedEntityBundle';
    }
}