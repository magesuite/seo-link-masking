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

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
        $this->registry = $registry;
        $this->configuration = $configuration;
    }

    public function afterGetSwatchData(\Magento\Swatches\Block\LayeredNavigation\RenderLayered $subject, $result)
    {
        $result['is_link_masking_enabled'] = false;

        $category = $this->getCategory();

        if (empty($category)) {
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

        return ($category && $category->getId()) ? $category : null;
    }
}
