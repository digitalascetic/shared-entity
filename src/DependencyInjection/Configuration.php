<?php

namespace DigitalAscetic\SharedEntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {

    $treeBuilder = new TreeBuilder('digital_ascetic_shared_entity');

    // Configuration name must match bundle name DigitalAsceticSharedEntity[Bundle]
    $rootNode = $treeBuilder->getRootNode();

    $rootNode
      ->children()
        ->booleanNode('enabled')->defaultValue(false)->end()
        ->booleanNode('index_source')->defaultValue(true)->end()
        ->scalarNode('origin')->defaultValue('default')->end()
      ->end();

    return $treeBuilder;

  }

}
