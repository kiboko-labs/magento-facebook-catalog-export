<?php

namespace Kiboko\Component\FacebookCatalog\Serializer\Normalizer;

use Kiboko\Component\MagentoDriver\Model\IntegerAttributeValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OptionsBackendNormalizer implements NormalizerInterface
{
    /**
     * @param IntegerAttributeValueInterface $object
     * @param string $format
     * @param array $context
     * @return string
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getValue();
    }

    /**
     * @param mixed $data
     * @param string $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof IntegerAttributeValueInterface;
    }
}