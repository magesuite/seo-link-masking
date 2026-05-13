<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\ViewModel;

class Category implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \Magento\Framework\Escaper $escaper;
    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;
    protected \MageSuite\SeoLinkMasking\Helper\Page $pageHelper;

    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Helper\Page $pageHelper
    ) {
        $this->escaper = $escaper;
        $this->configuration = $configuration;
        $this->pageHelper = $pageHelper;
    }

    public function isCategoryFilterMasked(): bool
    {
        if (!$this->configuration->isLinkMaskingEnabled()) {
            return false;
        }

        if (!$this->configuration->maskCategoryUrlOnSearchPage()) {
            return false;
        }

        return $this->pageHelper->isSearchResultPage();
    }

    public function isCategoryFilterAnchorless(): bool
    {
        if (!$this->isCategoryFilterMasked()) {
            return false;
        }

        return $this->configuration->isAnchorlessFilterLinksEnabled();
    }

    public function getCategoryFilterUrl(\Smile\ElasticsuiteCatalog\Model\Layer\Filter\Item\Category $filterItem): string
    {
        return $this->escaper->escapeUrl($filterItem->getUrl());
    }

    public function getCategoryFilterPostData(\Smile\ElasticsuiteCatalog\Model\Layer\Filter\Item\Category $filterItem): string
    {
        return (string)$filterItem->getUrl();
    }
}
