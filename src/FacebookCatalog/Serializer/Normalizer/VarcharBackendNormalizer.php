<?php

namespace Kiboko\Component\FacebookCatalog\Serializer\Normalizer;

use Kiboko\Component\MagentoDriver\Model\VarcharAttributeValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VarcharBackendNormalizer implements NormalizerInterface
{
    /**
     * @param VarcharAttributeValueInterface $object
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
        return $data instanceof VarcharAttributeValueInterface;
    }
}