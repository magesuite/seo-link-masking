<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Configuration
{
    public const LINK_MASKING_PARAMETER_REGISTRY_KEY = 'link_masking_parameters';

    public const XML_PATH_SEO_LINK_MASKING_IS_ENABLED = 'seo/link_masking/is_enabled';
    public const XML_PATH_SEO_LINK_MASKING_DEFAULT_MASKING_STATE = 'seo/link_masking/default_masking_state';
    public const XML_PATH_SEO_LINK_MASKING_ONLY_ONE_FILTER_DEMASKED = 'seo/link_masking/only_one_filter_demasked';
    public const XML_PATH_SEO_LINK_MASKING_MASK_CATEGORY_URL_ON_SEARCH_PAGE = 'seo/link_masking/mask_category_url_on_search_page';
    public const XML_PATH_SEO_LINK_MASKING_IS_SHORT_FILTER_URL_ENABLED = 'seo/link_masking/is_short_filter_url_enabled';
    public const XML_PATH_SEO_LINK_MASKING_IS_DISPLAYING_WARNING_ABOUT_DUPLICATED_OPTIONS_ENABLED = 'seo/link_masking/is_displaying_warning_about_duplicated_options_enabled';
    public const XML_PATH_SEO_LINK_MASKING_CACHE_LENGTH_FOR_WARNING_ABOUT_DUPLICATED_OPTIONS = 'seo/link_masking/cache_length_for_warning_about_duplicated_options';
    public const XML_PATH_SEO_LINK_MASKING_ENABLE_FILTER_PARAMS_IN_CANONICAL = 'seo/link_masking/enable_filter_params_in_canonical';
    public const XML_PATH_SEO_LINK_MASKING_SPACE_REPLACEMENT_CHAR = 'seo/link_masking/space_replacement_character';
    public const XML_PATH_SEO_LINK_MASKING_MULTISELECT_OPTION_SEPARATOR = 'seo/link_masking/multiselect_option_separator';
    public const XML_PATH_SEO_LINK_MASKING_IS_UTF_FRIENDLY_MODE_ENABLED = 'seo/link_masking/is_utf_friendly_mode_enabled';
    public const XML_PATH_SEO_LINK_MASKING_EXCLUDED_CHARACTERS = 'seo/link_masking/excluded_characters';

    public const XML_PATH_SHOW_SWATCH_TOOLTIP = 'catalog/frontend/show_swatch_tooltip';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isLinkMaskingEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getDefaultMaskingState($storeId = null): bool
    {
        if (!$this->isLinkMaskingEnabled()) {
            return false;
        }

        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_DEFAULT_MASKING_STATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function onlyOneFilterDemasked($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_ONLY_ONE_FILTER_DEMASKED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function maskCategoryUrlOnSearchPage($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_MASK_CATEGORY_URL_ON_SEARCH_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isShortFilterUrlEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_IS_SHORT_FILTER_URL_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isDisplayingWarningAboutDuplicatedOptionsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_IS_DISPLAYING_WARNING_ABOUT_DUPLICATED_OPTIONS_ENABLED);
    }

    public function getCacheLengthForWarningAboutDuplicatedOptions(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_CACHE_LENGTH_FOR_WARNING_ABOUT_DUPLICATED_OPTIONS);
    }

    public function areFilterParamsInCanonicalEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_ENABLE_FILTER_PARAMS_IN_CANONICAL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSpaceReplacementCharacter(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_SPACE_REPLACEMENT_CHAR);
    }

    public function getMultiselectOptionSeparator($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_SEO_LINK_MASKING_MULTISELECT_OPTION_SEPARATOR, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isUtfFriendlyModeEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEO_LINK_MASKING_IS_UTF_FRIENDLY_MODE_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getExcludedCharacters(): array
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

    public function canShowSwatchTooltip($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHOW_SWATCH_TOOLTIP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
