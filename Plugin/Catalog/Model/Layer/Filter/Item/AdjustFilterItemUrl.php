<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\Item;

class AdjustFilterItemUrl
{
    const CATEGORY_FILTER_CODE = 'cat';

    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;
    protected \MageSuite\SeoLinkMasking\Helper\Page $pageHelper;
    protected \Magento\Framework\App\RequestInterface $request;
    protected \Magento\Framework\Registry $registry;
    protected \Magento\Framework\UrlInterface $url;
    protected \Magento\Framework\Data\Helper\PostHelper $postHelper;
    protected \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor $filterItemUrlProcessor;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Helper\Page $pageHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor $filterItemUrlProcessor
    ) {
        $this->configuration = $configuration;
        $this->pageHelper = $pageHelper;
        $this->request = $request;
        $this->registry = $registry;
        $this->url = $url;
        $this->postHelper = $postHelper;
        $this->filterItemUrlProcessor = $filterItemUrlProcessor;
    }

    public function aroundGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $subject, \Closure $proceed)
    {
        $filter = $subject->getFilter();
        $category = $this->getCategory();

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

        if (!$this->configuration->isShortFilterUrlEnabled()) {
            $url = $proceed();
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

    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $subject, \Closure $proceed)
    {
        $filter = $subject->getFilter();
        $category = $this->getCategory();

        if (!$this->configuration->isShortFilterUrlEnabled() || $this->isCategoryFilter($filter->getRequestVar())) {
            return $proceed();
        }

        return $this->filterItemUrlProcessor->prepareItemRemoveUrl($filter, $category, $subject->getLabel());
    }

    private function isCategoryFilter($filterCode)
    {
        return $filterCode === self::CATEGORY_FILTER_CODE;
    }

    protected function getCategory()
    {
        $category = $this->registry->registry('current_category');

        return ($category && $category->getId()) ? $category : null;
    }

    protected function maskCategoryUrlOnSearchPage($url): string
    {
        $linkMaskingUrl = $this->url->getUrl(\MageSuite\SeoLinkMasking\Plugin\Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Attribute\AddLinkMaskingToFilterData::LINK_MASKING_ENDPOINT);
        return $this->postHelper->getPostData($linkMaskingUrl, ['url' => $url]);
    }
}
