<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Category
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->configuration = $configuration;
    }

    public function getCategoryEntityForSearchResultPage($category)
    {
        if ($category && $category->getId()) {
            return $category;
        }

        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $rootCategory = $this->categoryRepository->get($rootCategoryId);

        if ($this->configuration->isSearchResultPageAjaxFilterCall()) {
            return $rootCategory;
        }

        if (!$this->configuration->isSearchResultPage()) {
            return null;
        }

        return $rootCategory;
    }
}
