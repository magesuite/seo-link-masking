<?php

namespace MageSuite\SeoLinkMasking\Model;

class FilterParametersProcessor
{
    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider
     */
    protected $filterableAttributeOptionsProvider;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FiltrableAttributeUtfFriendlyConverter
     */
    protected $filtrableAttributeUtfFriendlyConverter;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Url
     */
    protected $urlHelper;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider $filterableAttributeOptionsProvider,
        \MageSuite\SeoLinkMasking\Service\FiltrableAttributeUtfFriendlyConverter $filtrableAttributeUtfFriendlyConverter,
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
        $this->filterableAttributeOptionsProvider = $filterableAttributeOptionsProvider;
        $this->filtrableAttributeUtfFriendlyConverter = $filtrableAttributeUtfFriendlyConverter;
        $this->urlHelper = $urlHelper;
        $this->configuration = $configuration;
    }

    public function process($urlParameters, $storeId = null)
    {
        $parameters = ltrim($urlParameters, '/');
        $parameters = explode('/', $parameters);

        if(empty($parameters)){
            return false;
        }

        $options = $this->filterableAttributeOptionsProvider->getOptions($storeId);
        $filterParameters = [];

        foreach ($parameters as $parameter) {
            $preparedParameter = $this->prepareParameter($parameter, $options);

            if (!$preparedParameter) {
                continue;
            }

            $filterParameters[$preparedParameter['key']] = $preparedParameter['value'];
        }

        if (count($parameters) != count($filterParameters) ) {
            return false;
        }

        return $filterParameters;
    }

    public function processRewrite($urlParameters, $oldStoreId, $targetStoreId)
    {
        $filterParameters = $this->process($urlParameters, $oldStoreId);

        if(empty($filterParameters)){
            return false;
        }

        foreach ($filterParameters as $code => $value) {
            $filterParameters[$code] = $this->filterableAttributeOptionsProvider->rewriteOption($code, $value, $oldStoreId, $targetStoreId);
        }

        return $filterParameters;
    }

    public function toUrl($filterParameters){
        foreach($filterParameters as $code => $values){
            $value = implode($this->configuration->getMultiselectOptionSeparator(), $values);
            $filterParameters[$code] = $this->urlHelper->encodeValue($value);
        }

        return '/' . implode('/', $filterParameters);
    }

    protected function prepareParameter($parameter, $options)
    {
        if (empty($parameter)) {
            return null;
        }
        
        if (strpos($parameter, $this->configuration->getMultiselectOptionSeparator()) === false) {
            if (!isset($options[$parameter])) {
                if($this->configuration->isUtfFriendlyModeEnabled()) {
                    $optionsConverted = $this->filtrableAttributeUtfFriendlyConverter->convertOptions($options);

                    if (isset($optionsConverted[$parameter])) {
                        return ['key' => $optionsConverted[$parameter]['code'], 'value' => $optionsConverted[$parameter]['value']];
                    }
                }
                return null;
            }

            return ['key' => $options[$parameter]['code'], 'value' => $options[$parameter]['value']];
        }

        $parameterOptions = explode($this->configuration->getMultiselectOptionSeparator(), $parameter);

        $key = null;
        $values = [];

        foreach ($parameterOptions as $parameterOption) {
            if (!isset($options[$parameterOption])) {
                if($this->configuration->isUtfFriendlyModeEnabled()) {
                    $optionsConverted = $this->filtrableAttributeUtfFriendlyConverter->convertOptions($options);

                    if(!isset($optionsConverted[$parameterOption])) {
                        return null;
                    }

                    $key = $optionsConverted[$parameterOption]['code'];
                    $values[] = $optionsConverted[$parameterOption]['value'];
                    continue;
                }
                return null;
            }

            $key = $options[$parameterOption]['code'];
            $values[] = $options[$parameterOption]['value'];
        }

        if (empty($key)) {
            return null;
        }

        return ['key' => $key, 'value' => $values];
    }
}
