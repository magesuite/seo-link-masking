<?php

namespace MageSuite\SeoLinkMasking\Model;

class FilterParametersProcessor
{
    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider
     */
    protected $filterableAttributeOptionsProvider;

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
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
        $this->filterableAttributeOptionsProvider = $filterableAttributeOptionsProvider;
        $this->urlHelper = $urlHelper;
        $this->configuration = $configuration;
    }

    public function process($parameters)
    {
        $options = $this->filterableAttributeOptionsProvider->getOptions();

        $filterParameters = [];

        foreach ($parameters as $parameter) {
            $preparedParameter = $this->prepareParameter($parameter, $options);

            if (!$preparedParameter) {
                continue;
            }

            $filterParameters[$preparedParameter['key']] = $preparedParameter['value'];
        }

        return $filterParameters;
    }

    protected function prepareParameter($parameter, $options)
    {
        if (strpos($parameter, $this->configuration->getMultiselectOptionSeparator()) === false) {
            if (!isset($options[$parameter])) {
                return null;
            }

            return ['key' => $options[$parameter]['code'], 'value' => $this->urlHelper->decodeValue($parameter)];
        }

        $parameterOptions = explode($this->configuration->getMultiselectOptionSeparator(), $parameter);

        $key = null;
        $values = [];

        foreach ($parameterOptions as $parameterOption) {
            if (!isset($options[$parameterOption])) {
                continue;
            }

            $key = $options[$parameterOption]['code'];
            $values[] = $this->urlHelper->decodeValue($parameterOption);
        }

        if (empty($key)) {
            return null;
        }

        return ['key' => $key, 'value' => $values];
    }
}
