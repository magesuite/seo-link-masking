<?php

namespace MageSuite\SeoLinkMasking\Plugin\Swatches\Block\LayeredNavigation\RenderLayered;

class AddLinkMaskingToSwatchData
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

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Category
     */
    protected $categoryHelper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Helper\Filter $filterHelper,
        \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper
    ) {
        $this->registry = $registry;
        $this->configuration = $configuration;
        $this->filterHelper = $filterHelper;
        $this->categoryHelper = $categoryHelper;
    }

    public function afterGetSwatchData(\Magento\Swatches\Block\LayeredNavigation\RenderLayered $subject, $result)
    {
        $result['is_link_masking_enabled'] = false;

        $category = $this->getCategory();

        if (empty($category)) {
            return $result;
        }

        if ($this->configuration->onlyOneFilterDemasked() && $this->filterHelper->isFilterSelected($category)) {
            $result['is_link_masking_enabled'] = true;
            return $result;
        }

        $seoLinkMasking = $category->getSeoLinkMasking();

        if (empty($seoLinkMasking)) {
            $result['is_link_masking_enabled'] = $this->configuration->getDefaultMaskingState();
            return $result;
        }

        $attributeId = $result['attribute_id'];

        if (isset($seoLinkMasking[$attributeId])) {
            $result['is_link_masking_enabled'] =  $seoLinkMasking[$attributeId];
            return $result;
        }

        $result['is_link_masking_enabled'] = $this->configuration->getDefaultMaskingState();
        return $result;
    }

    protected function getCategory()
    {
        $category = $this->registry->registry('current_category');
        return $this->categoryHelper->getCategoryEntityForSearchResultPage($category);
    }
}
