<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\AbstractFilter;

class IsLinkMaskingEnabled
{
    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\SeoLinkMasking\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function aroundGetData(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $subject, \Closure $proceed, $key = '', $index = null)
    {
        if ($key != 'is_link_masking_enabled') {
            return $proceed($key, $index);
        }

        return $this->configuration->isLinkMaskingEnabled();
    }
}
