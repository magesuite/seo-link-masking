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

        if (empty($parameters)) {
            return false;
        }

        $options = $this->filterableAttributeOptionsProvider->getOptions($storeId);

        $filterParameters = [];
        $filterParameterItemsCount = 0;

        foreach ($parameters as $parameter) {
            $preparedParameter = $this->prepareParameter($parameter, $options);

            if (!$preparedParameter) {
                continue;
            }

            $filterParameters = $this->addFilteredParameter($filterParameters, $preparedParameter);
            $filterParameterItemsCount++;
        }

        if (count($parameters) != $filterParameterItemsCount) {
            return false;
        }

        return $filterParameters;
    }

    public function processRewrite($urlParameters, $oldStoreId, $targetStoreId)
    {
        $filterParameters = $this->process($urlParameters, $oldStoreId);

        if (empty($filterParameters)) {
            return false;
        }

        foreach ($filterParameters as $code => $value) {
            $parameterOptions = new \Magento\Framework\DataObject([
                'code' => $code,
                'value' => $value,
                'old_store_id' => $oldStoreId,
                'target_store_id' => $targetStoreId
            ]);
            $filterParameters[$code] = $this->filterableAttributeOptionsProvider->rewriteOption($parameterOptions);
        }

        return $filterParameters;
    }

    public function toUrl($filterParameters)
    {
        foreach ($filterParameters as $code => $values) {
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

        $multiselectOptionSeparator = $this->configuration->getMultiselectOptionSeparator();

        if (strpos($parameter, $multiselectOptionSeparator) === false) {
            return $this->getFilterValues($parameter, $options);
        }

        $parameterOptions = explode($multiselectOptionSeparator, $parameter);

        $key = null;
        $values = [];

        foreach ($parameterOptions as $parameterOption) {
            $filterValues = $this->getFilterValues($parameterOption, $options);

            if ($filterValues === null) {
                return null;
            }

            $key = $filterValues['key'];
            $values[] = $filterValues['value'];
        }

        if (empty($key)) {
            return null;
        }

        return ['key' => $key, 'value' => $values];
    }

    protected function addFilteredParameter($filterParameters, $preparedParameter)
    {
        $preparedParameterKey = $preparedParameter['key'];
        $preparedParameterValue = $preparedParameter['value'];

        if (!isset($filterParameters[$preparedParameterKey])) {
            $filterParameters[$preparedParameterKey] = $preparedParameterValue;
            return $filterParameters;
        }

        if (!is_array($filterParameters[$preparedParameterKey])) {
            $filterParameters[$preparedParameterKey] = [$filterParameters[$preparedParameterKey]];
        }

        $filterParameters[$preparedParameterKey][] = $preparedParameterValue;
        return $filterParameters;
    }

    protected function getFilterValues(string $parameter, array $options): ?array
    {
        $utfFriendlyModeEnabled = $this->configuration->isUtfFriendlyModeEnabled();

        if (!isset($options[$parameter])) {
            $optionsConverted = $this->filtrableAttributeUtfFriendlyConverter->convertOptions($options);

            if ($utfFriendlyModeEnabled && isset($optionsConverted[$parameter])) {
                return [
                    'key' => $optionsConverted[$parameter]['code'],
                    'value' => $optionsConverted[$parameter]['value']
                ];
            }

            return null;
        }

        return [
            'key' => $options[$parameter]['code'],
            'value' => $options[$parameter]['value']
        ];
    }
}
