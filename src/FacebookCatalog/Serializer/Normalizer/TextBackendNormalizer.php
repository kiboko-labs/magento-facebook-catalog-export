<?php

namespace Kiboko\Component\FacebookCatalog\Serializer\Normalizer;

use Kiboko\Component\MagentoDriver\Model\TextAttributeValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextBackendNormalizer implements NormalizerInterface
{
    /**
     * @param TextAttributeValueInterface $object
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
        return $data instanceof TextAttributeValueInterface;
    }
}