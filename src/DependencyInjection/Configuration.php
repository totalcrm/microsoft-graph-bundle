<?php

namespace TotalCRM\MicrosoftGraph\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package TotalCRM\MicrosoftGraph\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('microsoft_graph');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('tenant_id')->end()
                ->scalarNode('client_id')->end()
                ->scalarNode('client_secret')->end()
                ->scalarNode('redirect_uri')->end()
                ->scalarNode('subscription_uri')->end()
                ->scalarNode('homepage_route')->end()
                ->scalarNode('prefer_time_zone')->end()
                ->scalarNode('version')->end()
                ->scalarNode('stateless')->end()
                ->scalarNode('contact_folder')->end()
                    ->variableNode('scopes')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
