<?php

namespace Kiboko\Bundle\FacebookCatalogBundle;

use Kiboko\Bundle\FacebookCatalogBundle\DependencyInjection\CompilerPass\DoctrineDbalRepositoriesCompilerPass;
use Kiboko\Bundle\FacebookCatalogBundle\DependencyInjection\CompilerPass\ProductAttributeValuesFactoriesCompilerPass;
use Kiboko\Bundle\FacebookCatalogBundle\DependencyInjection\CompilerPass\ProductFactoriesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KibokoFacebookCatalogBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineDbalRepositoriesCompilerPass());
        $container->addCompilerPass(new ProductFactoriesCompilerPass());
        $container->addCompilerPass(new ProductAttributeValuesFactoriesCompilerPass());
    }
}