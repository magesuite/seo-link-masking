<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SEO_LINK_MASKING_CONFIGURATION = 'seo/link_masking';
    const XML_PATH_SEO_LINK_MASKING_SPACE_REPLACEMENT_CHAR = 'seo/link_masking/space_replacement_character';
    const XML_PATH_SEO_LINK_MASKING_EXCLUDED_CHARACTERS = 'seo/link_masking/excluded_characters';
    const LINK_MASKING_PARAMETER_REGISTRY_KEY = 'link_masking_parameters';

    const SEARCH_RESULT_PAGE_FULL_ACTION_NAME = 'catalogsearch_result_index';

    const AJAX_FILTER_FULL_ACTION_NAME = 'catalog_navigation_filter_ajax';

    const XML_PATH_SHOW_SWATCH_TOOLTIP = 'catalog/frontend/show_swatch_tooltip';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $config = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);

        $this->scopeConfig = $scopeConfigInterface;
        $this->request = $request;
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

    public function onlyOneFilterDemasked()
    {
        return (bool)$this->getConfig()->getOnlyOneFilterDemasked();
    }

    public function isShortFilterUrlEnabled()
    {
        return (bool)$this->getConfig()->getIsShortFilterUrlEnabled();
    }

    public function areFilterParamsInCanonicalEnabled()
    {
        return (bool)$this->getConfig()->getEnableFilterParamsInCanonical();
    }

    public function getSpaceReplacementCharacter()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_SPACE_REPLACEMENT_CHAR);
    }

    public function getMultiselectOptionSeparator()
    {
        return $this->getConfig()->getMultiselectOptionSeparator();
    }

    public function isSearchResultPage()
    {
        return $this->request->getFullActionName() == self::SEARCH_RESULT_PAGE_FULL_ACTION_NAME;
    }

    public function isSearchResultPageAjaxFilterCall()
    {
        return !$this->request->getParam('cat') && ($this->request->getFullActionName() == self::AJAX_FILTER_FULL_ACTION_NAME);
    }

    public function isUtfFriendlyModeEnabled()
    {
        return (bool)$this->getConfig()->getIsUtfFriendlyModeEnabled();
    }

    public function getExcludedCharacters()
    {
        $excludedCharacters = $this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_EXCLUDED_CHARACTERS);

        if (empty($excludedCharacters)) {
            return [];
        }

        return array_map(
            'trim',
            str_split($excludedCharacters)
        );
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

    public function canShowSwatchTooltip()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SHOW_SWATCH_TOOLTIP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
