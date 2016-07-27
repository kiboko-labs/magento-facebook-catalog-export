<?php

namespace Kiboko\Component\FacebookCatalog\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\RuntimeErrorException;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Kiboko\Component\Connector\ConfigurationAwareTrait;
use Kiboko\Component\Connector\NameAwareTrait;
use Kiboko\Component\Connector\StepExecutionAwareTrait;
use Kiboko\Component\MagentoDriver\Entity\Product\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FacebookCatalogProcessor
    extends AbstractConfigurableStepElement
    implements ItemProcessorInterface, StepExecutionAwareInterface
{
    use NameAwareTrait;
    use ConfigurationAwareTrait;
    use StepExecutionAwareTrait;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * FacebookCatalogProcessor constructor.
     * @param NormalizerInterface $normalizer
     */
    public function __construct(
        NormalizerInterface $normalizer
    ) {
        $this->normalizer = $normalizer;
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @param ProductInterface $item
     * @return array
     */
    public function process($item)
    {
        if (!$item instanceof ProductInterface) {
            throw new RuntimeErrorException('Item should be of type %expected%, actual type was %actual%',
                [
                    '%expected%' => ProductInterface::class,
                    '%actual%'  => is_object($item) ? get_class($item) : gettype($item),
                ]
            );
        }

        if (!$this->normalizer->supportsNormalization($item)) {
            throw new RuntimeErrorException('Item could not be normalized, preconditions failed.');
        }

        return $this->normalizer->normalize($item);
    }
}