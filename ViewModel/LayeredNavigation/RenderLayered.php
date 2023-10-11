<?php

namespace MageSuite\SeoLinkMasking\ViewModel\LayeredNavigation;

class RenderLayered implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;

    public function __construct(\MageSuite\SeoLinkMasking\Helper\Configuration $configuration) {
        $this->configuration = $configuration;
    }

    public function canShowSwatchTooltip(): bool
    {
        return $this->configuration->canShowSwatchTooltip();
    }
}
