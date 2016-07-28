<?php

namespace Kiboko\Bundle\FacebookCatalogBundle\DependencyInjection\CompilerPass;

use Kiboko\Component\MagentoDriver\Matcher\AttributeValue\BackendTypeAttributeValueMatcher;
use Kiboko\Component\MagentoDriver\Matcher\AttributeValue\FrontendAndBackendTypeAttributeValueMatcher;
use Kiboko\Component\MagentoDriver\Matcher\AttributeValue\FrontendTypeAttributeValueMatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineDbalRepositoriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('kiboko.facebook_catalog.repository.broker')) {
            return;
        }
        $definition = $container->getDefinition(
            'kiboko.facebook_catalog.repository.broker'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'kiboko.facebook_catalog.repository.product_attribute_value'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['frontend']) && isset($attributes['backend'])) {
                    $matcherDefinition = new Definition(
                        FrontendAndBackendTypeAttributeValueMatcher::class,
                        [
                            $attributes['frontend'],
                            $attributes['backend'],
                        ]
                    );
                } elseif (isset($attributes['frontend'])) {
                    $matcherDefinition = new Definition(
                        FrontendTypeAttributeValueMatcher::class,
                        [
                            $attributes['frontend'],
                        ]
                    );
                } elseif (isset($attributes['backend'])) {
                    $matcherDefinition = new Definition(
                        BackendTypeAttributeValueMatcher::class,
                        [
                            $attributes['backend'],
                        ]
                    );
                } else {
                    continue;
                }

                $matcherDefinition->setPublic(false);

                $definition->addMethodCall(
                    'addRepository',
                    [new Reference($id), $matcherDefinition]
                );
            }
        }
    }
}