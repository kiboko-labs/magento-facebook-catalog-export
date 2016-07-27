<?php

namespace Kiboko\Component\FacebookCatalog\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Kiboko\Component\Connector\ConfigurationAwareTrait;
use Kiboko\Component\Connector\NameAwareTrait;
use Kiboko\Component\Connector\StepExecutionAwareTrait;
use Kiboko\Component\MagentoDriver\Exception\RuntimeErrorException;
use Kiboko\Component\MagentoDriver\Hydrator\ProductAttributeValueHydratorInterface;
use Kiboko\Component\MagentoDriver\Model\AttributeInterface;
use Kiboko\Component\MagentoDriver\Repository\ProductRepositoryInterface;
use Kiboko\Component\MagentoDriver\Repository\CategoryRepositoryInterface;
use Kiboko\Component\FacebookCatalog\Manager\CategoryManager;

class ProductReader
    extends AbstractConfigurableStepElement
    implements ItemReaderInterface, StepExecutionAwareInterface
{
    use NameAwareTrait;
    use ConfigurationAwareTrait;
    use StepExecutionAwareTrait;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeValueHydratorInterface
     */
    private $productHydrator;

    /**
     * @var AttributeInterface[]
     */
    private $attributeList;

    /**
     * @var \Iterator
     */
    private $iterator;

    /**
     * ProductByCategoryReader constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAttributeValueHydratorInterface $productHydrator
     * @param AttributeInterface[] $attributeList
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductAttributeValueHydratorInterface $productHydrator,
        array $attributeList
    ) {
        $this->productRepository = $productRepository;
        $this->productHydrator = $productHydrator;
        $this->attributeList = $attributeList;
    }

    public function getConfigurationFields()
    {
        return [];
    }

    public function initialize()
    {
        $traversable = $this->productRepository->findAll();
        if ($traversable instanceof \Iterator) {
            $this->iterator = $traversable;
        } else if ($traversable instanceof \IteratorAggregate) {
            $this->iterator = $traversable->getIterator();
        } else if (is_array($traversable)) {
            $this->iterator = new \ArrayIterator($traversable);
        } else {
            throw new RuntimeErrorException('Invalid iterator type: %actual%, expecting %expected%',
                [
                    '%expected%' => \Traversable::class,
                    '%actual%'   => is_object($traversable) ? get_class($traversable) : gettype($traversable),
                ]
            );
        }
    }

    public function read()
    {
        $this->iterator->next();
        if (!$this->iterator->valid()) {
            return null;
        }

        $product = $this->iterator->current();

        $this->productHydrator->hydrateByAttributeList($product, $this->attributeList);
        
        return $product;
    }
}