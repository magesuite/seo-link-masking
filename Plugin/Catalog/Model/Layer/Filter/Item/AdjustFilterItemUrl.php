<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\Item;

class AdjustFilterItemUrl
{
    const CATEGORY_FILTER_CODE = 'cat';

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postHelper;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor
     */
    protected $filterItemUrlProcessor;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor $filterItemUrlProcessor
    ) {
        $this->configuration = $configuration;
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

        if (!$this->configuration->isShortFilterUrlEnabled() || $this->isCategoryFilter($filter->getRequestVar())) {
            return $proceed();
        }

        $url = $this->filterItemUrlProcessor->prepareItemUrl($filter, $category, $subject->getValue());

        if ($this->request->getFullActionName() != \MageSuite\SeoLinkMasking\Helper\Configuration::AJAX_FILTER_FULL_ACTION_NAME || !$filter->getIsLinkMaskingEnabled()) {
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
}
