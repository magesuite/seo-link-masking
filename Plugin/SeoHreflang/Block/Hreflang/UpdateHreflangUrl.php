<?php

namespace MageSuite\SeoLinkMasking\Plugin\SeoHreflang\Block\Hreflang;

class UpdateHreflangUrl
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    public function aroundAddQueryToUrl(\MageSuite\SeoHreflang\Block\Hreflang $subject, \Closure $proceed, $url)
    {
        $linkMaskingParameters = $this->registry->registry(\MageSuite\SeoLinkMasking\Helper\Configuration::LINK_MASKING_PARAMETER_REGISTRY_KEY);

        if (empty($linkMaskingParameters)) {
            return $proceed($url);
        }

        return $url . $linkMaskingParameters;
    }
}