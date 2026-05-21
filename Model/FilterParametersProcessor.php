<?php

namespace MageSuite\SeoLinkMasking\Model;

class FilterParametersProcessor
{
    public function __construct(
        protected \MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider $filterableAttributeOptionsProvider,
        protected \MageSuite\SeoLinkMasking\Service\FiltrableAttributeUtfFriendlyConverter $filtrableAttributeUtfFriendlyConverter,
        protected \MageSuite\SeoLinkMasking\Helper\Url $urlHelper,
        protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
    }

    public function process(string $urlParameters, ?int $storeId = null): ?array
    {
        $parameters = ltrim($urlParameters, '/');
        $parameters = explode('/', $parameters);

        if (empty($parameters)) {
            return null;
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
            return null;
        }

        return $filterParameters;
    }

    public function processRewrite(string $urlParameters, int $oldStoreId, int $targetStoreId): ?array
    {
        $filterParameters = $this->process($urlParameters, $oldStoreId);

        if (empty($filterParameters)) {
            return null;
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

    public function toUrl(array $filterParameters): string
    {
        $separator = $this->configuration->getMultiselectOptionSeparator();

        foreach ($filterParameters as $code => $values) {
            if (!is_array($values)) {
                $filterParameters[$code] = $this->urlHelper->encodeValue($values);
                continue;
            }

            $values = array_map([$this->urlHelper, 'encodeValue'], $values);
            $filterParameters[$code] = implode($separator, $values);
        }

        return '/' . implode('/', $filterParameters);
    }

    protected function prepareParameter(string $parameter, array $options): ?array
    {
        if (empty($parameter)) {
            return null;
        }

        $multiselectOptionSeparator = $this->configuration->getMultiselectOptionSeparator();

        if (!str_contains($parameter, $multiselectOptionSeparator)) {
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

    protected function addFilteredParameter(array $filterParameters, array $preparedParameter): array
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
