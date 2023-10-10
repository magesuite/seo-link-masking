<?php

namespace MageSuite\SeoLinkMasking\ViewModel;

class Category implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public const REGULAR_CAT_FILTER_URL_FORMAT = 'href="%s"';
    public const MASKED_CAT_FILTER_URL_FORMAT = 'href="#" data-post="%s"';

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

    public function getCategoryFilterUrl(\Smile\ElasticsuiteCatalog\Model\Layer\Filter\Item\Category $filterItem): ?string
    {
        $url = $this->escaper->escapeUrl($filterItem->getUrl());
        $format = self::REGULAR_CAT_FILTER_URL_FORMAT;

        if ($this->configuration->maskCategoryUrlOnSearchPage() && $this->pageHelper->isSearchResultPage()) {
            $format = self::MASKED_CAT_FILTER_URL_FORMAT;
        }

        return sprintf($format, $url);
    }
}
