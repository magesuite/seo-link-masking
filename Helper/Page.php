<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Page
{
    const SEARCH_RESULT_PAGE_FULL_ACTION_NAME = 'catalogsearch_result_index';
    const BRAND_PAGE_FULL_ACTION_NAME = 'brands_index_index';
    const AJAX_FILTER_FULL_ACTION_NAME = 'catalog_navigation_filter_ajax';

    protected \Magento\Framework\App\Request\Http $request;

    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
    }

    public function isSearchResultPage(): bool
    {
        return $this->request->getFullActionName() == self::SEARCH_RESULT_PAGE_FULL_ACTION_NAME;
    }

    public function isBrandsIndexPage(): bool
    {
        return $this->request->getFullActionName() == self::BRAND_PAGE_FULL_ACTION_NAME;
    }

    public function isSearchResultPageAjaxFilterCall(): bool
    {
        return !$this->request->getParam('cat') && ($this->request->getFullActionName() == self::AJAX_FILTER_FULL_ACTION_NAME);
    }
}
