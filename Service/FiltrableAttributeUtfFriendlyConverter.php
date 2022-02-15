<?php

namespace MageSuite\SeoLinkMasking\Service;

class FiltrableAttributeUtfFriendlyConverter
{
    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\SeoLinkMasking\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function convertOptions($options)
    {
        foreach ($options as $optionKey => $option) {
            $options[$this->convertFilterParam($optionKey)] = $option;
        }

        return $options;
    }

    public function convertFilterParams($filteredValues)
    {
        foreach ($filteredValues as &$filteredValue) {
            $filteredValue = $this->convertFilterParam($filteredValue);
        }

        return $filteredValues;
    }

    protected function convertFilterParam($filteredValue)
    {
        $filteredValue = urldecode($filteredValue);

        $convertTable = [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ß' => 'ss',
            'ẞ' => 'Ss'
        ];

        $utfFriendlyParameter = str_replace(
            array_keys($convertTable),
            array_values($convertTable),
            $filteredValue
        );

        return $this->encodeUrl($utfFriendlyParameter);
    }

    /**
     * Special case: if url contain /, it shouldn't be encoded to %2F
     */
    protected function encodeUrl($utfFriendlyParameter)
    {
        if (empty($this->configuration->getExcludedCharacters()) || !in_array('/', $this->configuration->getExcludedCharacters())) {
            return urlencode($utfFriendlyParameter);
        }

        if (strpos($utfFriendlyParameter, '/') === false) {
            return urlencode($utfFriendlyParameter);
        }

        $paramParts = explode('/', $utfFriendlyParameter);

        foreach ($paramParts as &$part) {
            $part = urlencode($part);
        }

        return implode('/', $paramParts);
    }
}
