<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\AbstractFilter;

class IsLinkMaskingEnabled
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Filter
     */
    protected $filterHelper;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Category
     */
    protected $categoryHelper;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Helper\Filter $filterHelper,
        \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->configuration = $configuration;
        $this->filterHelper = $filterHelper;
        $this->categoryHelper = $categoryHelper;
    }

    public function aroundGetData(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $subject, \Closure $proceed, $key = '', $index = null)
    {
        if ($key != 'is_link_masking_enabled' || !$subject->hasAttributeModel()) {
            return $proceed($key, $index);
        }

        $category = $this->getCategory($subject);

        if (empty($category)) {
            return $proceed($key, $index);
        }

        $attributeId = $subject->getAttributeModel()->getId();

        return $this->filterHelper->isFilterMasked($category, $attributeId);
    }

    protected function getCategory($subject)
    {
        if ($this->request->getFullActionName() == \MageSuite\SeoLinkMasking\Helper\Configuration::AJAX_FILTER_FULL_ACTION_NAME) {
            return $subject->getLayer()->getCurrentCategory();
        }

        $category = $this->registry->registry('current_category');
        return $this->categoryHelper->getCategoryEntityForSearchResultPage($category);
    }
}
