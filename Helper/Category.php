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

        if ($this->configuration->isSearchResultPageAjaxFilterCall() ||
            $this->configuration->isSearchResultPage() ||
            $this->configuration->isBrandsIndexPage()
        ) {
            $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
            return $this->categoryRepository->get($rootCategoryId);
        }

        return null;
    }
}
