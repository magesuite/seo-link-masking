<?php

namespace MageSuite\SeoLinkMasking\Plugin\SeoHreflang\Block\Hreflang;

class UpdateHreflangUrl
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

    public function aroundAddQueryToUrl(\MageSuite\SeoHreflang\Block\Hreflang $subject, \Closure $proceed, $url)
    {
        $linkMaskingParameters = $this->registry->registry(\MageSuite\SeoLinkMasking\Helper\Configuration::LINK_MASKING_PARAMETER_REGISTRY_KEY);

        if (empty($linkMaskingParameters)) {
            return $proceed($url);
        }
        if($this->configuration->isUtfFriendlyModeEnabled()) {
            $queryParams = $subject->getRequest()->getQueryValue();
            $linkMaskingParameters = $this->buildUrlMaskFromQueryParams($queryParams);
        }

        return $url . $linkMaskingParameters;
    }

    protected function buildUrlMaskFromQueryParams($queryParams)
    {
        $linkMaskingParameters = '';
        $separator = $this->configuration->getMultiselectOptionSeparator();
        foreach ($queryParams as $param)
        {
            if(!is_array($param)) {
                $param = '/'.strtolower($param);
                $linkMaskingParameters = $linkMaskingParameters.$param;
                continue;
            }

            $multiParam = '/'.implode($separator,$param);
            $linkMaskingParameters = $linkMaskingParameters.$multiParam;

        }

        return $linkMaskingParameters;
    }
}