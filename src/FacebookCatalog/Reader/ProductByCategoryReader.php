<?php

namespace Kiboko\Component\FacebookCatalog\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Kiboko\Component\Connector\ConfigurationAwareTrait;
use Kiboko\Component\Connector\NameAwareTrait;
use Kiboko\Component\Connector\StepExecutionAwareTrait;
use Kiboko\Component\MagentoDriver\Exception\RuntimeErrorException;
use Kiboko\Component\MagentoDriver\Repository\ProductRepositoryInterface;
use Kiboko\Component\MagentoDriver\Repository\CategoryRepositoryInterface;
use Kiboko\Component\FacebookCatalog\Manager\CategoryManager;

class ProductByCategoryReader
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
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryManager
     */
    private $categoryManager;

    /**
     * @var \Iterator
     */
    private $iterator;

    /**
     * ProductByCategoryReader constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryManager $categoryManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository//,
        //CategoryRepositoryInterface $categoryRepository,
        //CategoryManager $categoryManager
    ) {
        $this->productRepository = $productRepository;
        //$this->categoryRepository = $categoryRepository;
        //$this->categoryManager = $categoryManager;
    }

    public function getConfigurationFields()
    {
        return [/*
            'category' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->categoryManager->getChildrenCategories(
                        $this->categoryRepository->findRootByStoreCode('default')
                    ),
                    'required' => true,
                    'select2'  => true,
                    'label' => 'kiboko_facebook_catalog.processor.category.label',
                    'help'  => 'kiboko_facebook_catalog.processor.category.help',
                ],
            ],*/
        ];
    }

    public function initialize()
    {
        //$category = $this->getConfiguration()['category'];

        //$traversable = $this->productRepository->findAllByCategory($category);
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

        return $this->iterator->current();
    }
}