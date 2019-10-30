<?php

namespace MageSuite\SeoLinkMasking\Observer;

class UpdateMetaRobotsTag implements \Magento\Framework\Event\ObserverInterface
{
    const ROBOTS_TAG_NOINDEX_FOLLOW = 'NOINDEX,FOLLOW';

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\MetaRobotsTag\FilterStateValidator
     */
    protected $filterStateValidator;

    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Service\MetaRobotsTag\FilterStateValidator $filterStateValidator
    ) {
        $this->pageConfig = $pageConfig;
        $this->configuration = $configuration;
        $this->filterStateValidator = $filterStateValidator;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isLinkMaskingEnabled()) {
            return;
        }

        $isMaskedFilterSelected = $this->filterStateValidator->isMaskedFilterSelected();

        if (!$isMaskedFilterSelected) {
            return;
        }

        $this->pageConfig->setRobots(self::ROBOTS_TAG_NOINDEX_FOLLOW);
    }
}
