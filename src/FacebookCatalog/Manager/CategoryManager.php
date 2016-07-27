<?php

namespace Kiboko\Component\FacebookCatalog\Manager;


use Kiboko\Component\MagentoDriver\Entity\CategoryInterface;
use Kiboko\Component\MagentoDriver\Repository\CategoryRepositoryInterface;

class CategoryManager
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * CategoryManager constructor.
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(
        CategoryRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }
    
    /**
     * Get a list of available categories
     *
     * @param CategoryInterface $category
     * @return CategoryInterface[]
     */
    public function getChildrenCategories(CategoryInterface $category)
    {
        $categoryLabels = [];
        foreach ($this->repository->findAllChildren($category) as $category) {
            $categoryLabels[] = $category;
        }
        return $categoryLabels;
    }
}