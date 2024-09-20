<?php

declare(strict_types=1);
namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\Item;

class AdjustFilterItemUrl
{
    protected const CATEGORY_FILTER_CODE = 'cat';

    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;
    protected \MageSuite\SeoLinkMasking\Helper\Page $pageHelper;
    protected \Magento\Framework\App\RequestInterface $request;
    protected \Magento\Framework\Registry $registry;
    protected \Magento\Framework\UrlInterface $url;
    protected \Magento\Framework\Data\Helper\PostHelper $postHelper;
    protected \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor $filterItemUrlProcessor;
    protected \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Helper\Page $pageHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor $filterItemUrlProcessor,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->configuration = $configuration;
        $this->pageHelper = $pageHelper;
        $this->request = $request;
        $this->registry = $registry;
        $this->url = $url;
        $this->postHelper = $postHelper;
        $this->filterItemUrlProcessor = $filterItemUrlProcessor;
        $this->categoryRepository = $categoryRepository;
    }

    public function aroundGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $subject, \Closure $proceed):string
    {
        $filter = $subject->getFilter();
        $maskingEnabled = $this->configuration->isShortFilterUrlEnabled() || $filter->getIsLinkMaskingEnabled();

        if (!$maskingEnabled) {
            return $proceed();
        }

        if ($this->isCategoryFilter($filter->getRequestVar())) {
            if ($this->configuration->maskCategoryUrlOnSearchPage() && $this->pageHelper->isSearchResultPage()) {
                return $this->maskCategoryUrlOnSearchPage($proceed());
            }

            return $proceed();
        }

        try {
            $category = $this->getCategory();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $proceed();
        }

        if (!$this->configuration->isShortFilterUrlEnabled()) {
            $url = $proceed();
        } elseif ($subject->getIsSelected()) {
            $url = $this->filterItemUrlProcessor->prepareItemRemoveUrl($filter, $category, $subject->getValue());
        } else {
            $url = $this->filterItemUrlProcessor->prepareItemUrl($filter, $category, $subject->getValue());
        }

        if ($this->request->getFullActionName() != \MageSuite\SeoLinkMasking\Helper\Page::AJAX_FILTER_FULL_ACTION_NAME) {
            return $url;
        }

        if (!$filter->getIsLinkMaskingEnabled()) {
            return $url;
        }

        $linkMaskingUrl = $this->url->getUrl(\MageSuite\SeoLinkMasking\Plugin\Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Attribute\AddLinkMaskingToFilterData::LINK_MASKING_ENDPOINT);

        return $this->postHelper->getPostData($linkMaskingUrl, ['url' => $url]);
    }

    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $subject, \Closure $proceed):string
    {
        $filter = $subject->getFilter();

        if (!$this->configuration->isShortFilterUrlEnabled() || $this->isCategoryFilter($filter->getRequestVar())) {
            return $proceed();
        }

        try {
            $category = $this->getCategory();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $proceed();
        }

        return $this->filterItemUrlProcessor->prepareItemRemoveUrl($filter, $category, $subject->getLabel());
    }

    protected function isCategoryFilter(string $filterCode):bool
    {
        return $filterCode === self::CATEGORY_FILTER_CODE;
    }

    protected function getCategory():?\Magento\Catalog\Api\Data\CategoryInterface
    {
        $category = $this->registry->registry('current_category');

        if ($category) {
            return $category;
        }

        $categoryId = (int)$this->request->getParam(self::CATEGORY_FILTER_CODE);

        if (!$categoryId) {
            return null;
        }

        return $this->categoryRepository->get($categoryId);
    }

    protected function maskCategoryUrlOnSearchPage($url): string
    {
        $linkMaskingUrl = $this->url->getUrl(\MageSuite\SeoLinkMasking\Plugin\Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Attribute\AddLinkMaskingToFilterData::LINK_MASKING_ENDPOINT);
        return $this->postHelper->getPostData($linkMaskingUrl, ['url' => $url]);
    }
}
