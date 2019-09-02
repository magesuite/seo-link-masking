<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SEO_LINK_MASKING_CONFIGURATION = 'seo/link_masking';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $config = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
    ) {
        parent::__construct($context);

        $this->scopeConfig = $scopeConfigInterface;
    }

    public function getDefaultMaskingState()
    {
        if (!$this->isLinkMaskingEnabled()) {
            return false;
        }

        return (bool)$this->getConfig()->getDefaultMaskingState();
    }

    public function isLinkMaskingEnabled()
    {
        return (bool)$this->getConfig()->getIsEnabled();
    }

    protected function getConfig()
    {
        if ($this->config === null) {
            $this->config = new \Magento\Framework\DataObject(
                $this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_CONFIGURATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        return $this->config;
    }
}
