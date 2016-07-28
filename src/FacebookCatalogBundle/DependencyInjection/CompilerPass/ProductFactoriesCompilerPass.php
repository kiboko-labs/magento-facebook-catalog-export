<?php

namespace Kiboko\Bundle\FacebookCatalogBundle\DependencyInjection\CompilerPass;

use Kiboko\Component\MagentoDriver\Matcher\Product\ProductTypeMatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ProductFactoriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('kiboko.facebook_catalog.factory.product.broker')) {
            return;
        }
        $definition = $container->getDefinition(
            'kiboko.facebook_catalog.factory.product.broker'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'kiboko.facebook_catalog.factory.product'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['type'])) {
                    continue;
                }

                $matcherDefinition = new Definition(
                    ProductTypeMatcher::class,
                    [
                        $attributes['type']
                    ]
                );

                $matcherDefinition->setPublic(false);

                $definition->addMethodCall(
                    'addFactory',
                    [new Reference($id), $matcherDefinition]
                );
            }
        }
    }
}