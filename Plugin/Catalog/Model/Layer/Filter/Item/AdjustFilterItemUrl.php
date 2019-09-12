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
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor
     */
    protected $filterItemUrlProcessor;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\Registry $registry,
        \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor $filterItemUrlProcessor
    ) {
        $this->configuration = $configuration;
        $this->registry = $registry;
        $this->filterItemUrlProcessor = $filterItemUrlProcessor;
    }

    public function aroundGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $subject, \Closure $proceed)
    {
        $filter = $subject->getFilter();
        $category = $this->getCategory();

        if (!$this->configuration->isShortFilterUrlEnabled() || $this->isCategoryFilter($filter->getRequestVar())) {
            return $proceed();
        }

        if (!$category) {
            return $proceed();
        }

        return $this->filterItemUrlProcessor->prepareItemUrl($filter, $category, $subject->getValue());
    }

    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $subject, \Closure $proceed)
    {
        $filter = $subject->getFilter();
        $category = $this->getCategory();

        if (!$this->configuration->isShortFilterUrlEnabled() || $this->isCategoryFilter($filter->getRequestVar())) {
            return $proceed();
        }

        if (!$category) {
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
