<?php

namespace Kiboko\Component\FacebookCatalog\Serializer\Normalizer;

use Kiboko\Component\MagentoDriver\Model\ImageAttributeValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ImageBackendNormalizer implements NormalizerInterface
{
    /**
     * @param ImageAttributeValueInterface $object
     * @param string $format
     * @param array $context
     * @return string
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getFile()->getPath();
    }

    /**
     * @param mixed $data
     * @param string $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ImageAttributeValueInterface;
    }
}