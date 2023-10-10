<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Category
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    protected \MageSuite\SeoLinkMasking\Helper\Page $pageHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \MageSuite\SeoLinkMasking\Helper\Page $pageHelper
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->pageHelper = $pageHelper;
    }

    public function getCategoryEntityForSearchResultPage($category)
    {
        if ($category && $category->getId()) {
            return $category;
        }

        if ($this->pageHelper->isSearchResultPageAjaxFilterCall() ||
            $this->pageHelper->isSearchResultPage() ||
            $this->pageHelper->isBrandsIndexPage()
        ) {
            $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
            return $this->categoryRepository->get($rootCategoryId);
        }

        return null;
    }
}
