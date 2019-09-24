<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\AbstractFilter;

class IsLinkMaskingEnabled
{
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

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Helper\Filter $filterHelper
    ) {
        $this->registry = $registry;
        $this->configuration = $configuration;
        $this->filterHelper = $filterHelper;
    }

    public function aroundGetData(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $subject, \Closure $proceed, $key = '', $index = null)
    {
        if ($key != 'is_link_masking_enabled' || !$subject->hasAttributeModel()) {
            return $proceed($key, $index);
        }

        $category = $this->getCategory();

        if (empty($category)) {
            return $proceed($key, $index);
        }

        if ($this->configuration->onlyOneFilterDemasked() && $this->filterHelper->isFilterSelected($category)) {
            return true;
        }

        $seoLinkMasking = $category->getSeoLinkMasking();

        if (empty($seoLinkMasking)) {
            return $this->configuration->getDefaultMaskingState();
        }

        $attributeId = $subject->getAttributeModel()->getId();

        if (isset($seoLinkMasking[$attributeId])) {
            return $seoLinkMasking[$attributeId];
        }

        return $this->configuration->getDefaultMaskingState();
    }

    protected function getCategory()
    {
        $category = $this->registry->registry('current_category');

        return ($category && $category->getId()) ? $category : null;
    }
}
