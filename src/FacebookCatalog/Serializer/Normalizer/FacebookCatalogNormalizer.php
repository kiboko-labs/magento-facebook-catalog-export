<?php

namespace Kiboko\Component\FacebookCatalog\Serializer\Normalizer;

use Kiboko\Component\MagentoDriver\Entity\Product\ProductInterface;
use Kiboko\Component\MagentoDriver\Model\AttributeInterface;
use Kiboko\Component\MagentoDriver\Model\DecimalAttributeValueInterface;
use Kiboko\Component\MagentoDriver\Repository\AttributeRepositoryInterface;
use Kiboko\Component\MagentoDriver\Repository\ProductAttributeValueRepositoryInterface;
use Kiboko\Component\MagentoDriver\Repository\ProductInventoryRepositoryInterface;
use Kiboko\Component\MagentoDriver\Repository\ProductUrlRewriteRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FacebookCatalogNormalizer implements NormalizerInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductInventoryRepositoryInterface
     */
    private $productInventoryRepository;

    /**
     * @var ProductUrlRewriteRepositoryInterface
     */
    private $productUrlRewriteRepository;

    /**
     * @var NormalizerInterface
     */
    private $nameNormalizer;

    /**
     * @var NormalizerInterface
     */
    private $descriptionNormalizer;

    /**
     * @var NormalizerInterface
     */
    private $manufacturerNormalizer;

    /**
     * @var NormalizerInterface
     */
    private $imageNormalizer;

    /**
     * FacebookCatalogNormalizer constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductInventoryRepositoryInterface $productInventoryRepository
     * @param ProductUrlRewriteRepositoryInterface $productUrlRewriteRepository
     * @param NormalizerInterface $nameNormalizer
     * @param NormalizerInterface $descriptionNormalizer
     * @param NormalizerInterface $manufacturerNormalizer
     * @param NormalizerInterface $imageNormalizer
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductInventoryRepositoryInterface $productInventoryRepository,
        ProductUrlRewriteRepositoryInterface $productUrlRewriteRepository,
        NormalizerInterface $nameNormalizer,
        NormalizerInterface $descriptionNormalizer,
        NormalizerInterface $manufacturerNormalizer,
        NormalizerInterface $imageNormalizer
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productInventoryRepository = $productInventoryRepository;
        $this->productUrlRewriteRepository = $productUrlRewriteRepository;

        $this->nameNormalizer = $nameNormalizer;
        $this->descriptionNormalizer = $descriptionNormalizer;
        $this->manufacturerNormalizer = $manufacturerNormalizer;
        $this->imageNormalizer = $imageNormalizer;
    }

    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$object instanceof ProductInterface) {
            return null;
        }

        return [
            'id'           => $this->normalizeIdentifier($object),
            'availability' => $this->normalizeAvailability($object),
            'condition'    => $this->normalizeCondition($object),
            'description'  => $this->normalizeDescription($object, isset($context['store']) ? $context['store'] : 0),
            'image_link'   => $this->normalizeImageLink($object, isset($context['store']) ? $context['store'] : 0),
            'link'         => $this->normalizeUrl($object),
            'title'        => $this->normalizeTitle($object, isset($context['store']) ? $context['store'] : 0),
            'price'        => $this->normalizePrice($object),
            'brand'        => $this->normalizeManufacturer($object, isset($context['store']) ? $context['store'] : 0),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface;
    }

    /**
     * @param ProductInterface $object
     * @return string
     */
    private function normalizeIdentifier(ProductInterface $object)
    {
        return sprintf('%d', $object->getId());
    }

    /**
     * @param ProductInterface $object
     * @return string
     */
    private function normalizeAvailability(ProductInterface $object)
    {
        if (null === ($inventory = $this->productInventoryRepository->findOneByProduct($object))) {
            return 'out of stock';
        }

        if (!$inventory->isStockManaged()) {
            return 'in stock';
        }

        if ($inventory->isAvailable()) {
            if ($inventory->getAvailableQty() > 0) {
                return 'in stock';
            }

            return 'available for order';
        }

        if ($inventory->canPreOrder()) {
            return 'preorder';
        }

        return 'out of stock';
    }

    /**
     * @param ProductInterface $object
     * @return string
     */
    private function normalizeCondition(ProductInterface $object)
    {
        return 'new';
    }

    /**
     * @param ProductInterface $object
     * @param AttributeInterface $attribute
     * @param int $storeId
     *
     * @return string
     */
    private function normalizeTitle(ProductInterface $object, $storeId)
    {
        if (null === ($value = $object->getValueByAttributeCode('name', $storeId))) {
            return null;
        }

        if ($this->nameNormalizer->supportsNormalization($value)) {
            return $this->nameNormalizer->normalize($value);
        }

        return null;
    }

    /**
     * @param ProductInterface $object
     * @param AttributeInterface $attribute
     * @param int $storeId
     *
     * @return string|null
     */
    private function normalizeDescription(ProductInterface $object, $storeId)
    {
        if (null === ($value = $object->getValueByAttributeCode('description', $storeId))) {
            return null;
        }

        if ($this->descriptionNormalizer->supportsNormalization($value)) {
            return $this->descriptionNormalizer->normalize($value);
        }

        return null;
    }

    /**
     * @param ProductInterface $object
     * @param AttributeInterface $attribute
     * @param int $storeId
     *
     * @return string
     */
    private function normalizeImageLink(ProductInterface $object, $storeId)
    {
        if (null === ($value = $object->getValueByAttributeCode('image', $storeId))) {
            return null;
        }

        if ($this->imageNormalizer->supportsNormalization($value)) {
            return $this->imageNormalizer->normalize($object);
        }

        return null;
    }

    /**
     * @param ProductInterface $object
     * @param AttributeInterface $attribute
     * @param int $storeId
     *
     * @return string|null
     */
    private function normalizeManufacturer(ProductInterface $object, $storeId)
    {
        if (null === ($value = $object->getValueByAttributeCode('manufacturer', $storeId))) {
            return null;
        }

        if ($this->descriptionNormalizer->supportsNormalization($value)) {
            return $this->descriptionNormalizer->normalize($object);
        }

        return null;
    }

    /**
     * @param ProductInterface $object
     * @return string
     */
    private function normalizePrice(ProductInterface $object)
    {
        if (null === ($price = $object->getValueByAttributeCode('price'))) {
            return null;
        }

        if (!$price instanceof DecimalAttributeValueInterface) {
            return null;
        }

        return sprintf('%s %s', number_format($price->getValue(), 2, '.', ''), 'EUR');
    }

    /**
     * @param ProductInterface $object
     * @return string
     */
    private function normalizeUrl(ProductInterface $object)
    {
        return sprintf('http://luni.fr/catalog/product/view/id/%d', $object->getId());

        if (null === ($url = $this->productUrlRewriteRepository->findOneByProduct($object))) {
            return null;
        }

        return $url->getUrl();
    }
}