<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterItemUrlProcessor
{
    const ATTRIBUTE_TYPE_SELECT = 'select';
    const ATTRIBUTE_TYPE_MULTISELECT = 'multiselect';
    const ATTRIBUTE_TYPE_SWATCH = 'swatch';

    public static $filterableAttributeTypes = [
        'select' => self::ATTRIBUTE_TYPE_SELECT,
        'multiselect' =>  self::ATTRIBUTE_TYPE_MULTISELECT,
        'swatch' => self::ATTRIBUTE_TYPE_SWATCH
    ];

    const URL_PARAMS_DELIMITER = '?';
    const PATH_SEPARATOR = '/';

    const MODE_ADD = 'add';
    const MODE_REMOVE = 'remove';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider
     */
    protected $filterableAttributesProvider;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Url
     */
    protected $urlHelper;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $filterableAttributes = [];

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $url,
        \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider $filterableAttributesProvider,
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->filterableAttributesProvider = $filterableAttributesProvider;
        $this->urlHelper = $urlHelper;
        $this->configuration = $configuration;
    }

    public function prepareItemUrl($filter, $category, $filterValue)
    {
        $requestParameters = $this->processRequestParameters($filter, $filterValue, self::MODE_ADD);

        return $this->prepareFilterUrl($requestParameters, $category);
    }

    public function prepareItemRemoveUrl($filter, $category, $filterValue)
    {
        $requestParameters = $this->processRequestParameters($filter, $filterValue, self::MODE_REMOVE);

        return $this->prepareFilterUrl($requestParameters, $category);
    }

    protected function processRequestParameters($filter, $filterValue, $mode)
    {
        $parameters = $this->request->getQueryValue();

        if ($mode == self::MODE_ADD) {
            return $this->addFilterValueToUrlParameters($filter, $filterValue, $parameters);
        } elseif ($mode == self::MODE_REMOVE) {
            return $this->removeFilterValueFromUrlParameters($filter, $filterValue, $parameters);
        }

        return [];
    }

    protected function prepareFilterUrl($requestParameters, $category)
    {
        $filterableAttributes = $this->getFilterableAttributes($category);

        $filtersValues = [];

        foreach ($requestParameters as $code => $value) {
            if (!isset($filterableAttributes[$code])) {
                continue;
            }

            $requestParameters[$code] = null;

            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $value = array_map([$this->urlHelper, 'encodeValue'], $value);
                $value = implode($this->configuration->getMultiselectOptionSeparator(), $value);
            } else {
                $value = $this->urlHelper->encodeValue($value);
            }

            $filtersValues[] = $value;
        }

        return $this->buildFilterUrl($requestParameters, $filtersValues);
    }

    protected function buildFilterUrl($requestParameters, $filtersValues)
    {
        $url = $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $requestParameters]);

        if (empty($filtersValues)) {
            return $url;
        }

        $urlParts = explode(self::URL_PARAMS_DELIMITER, $url);

        $url = rtrim($urlParts[0], self::PATH_SEPARATOR) . self::PATH_SEPARATOR . implode(self::PATH_SEPARATOR, $filtersValues);

        if (isset($urlParts[1])) {
            $url .= self::URL_PARAMS_DELIMITER . $urlParts[1];
        }

        return $this->url->escape($url);
    }

    private function getAttributeType($filter)
    {
        if (!$filter->hasAttributeModel()) {
            return null;
        }

        $attributeModel = $filter->getAttributeModel();

        if (!in_array($attributeModel->getFrontendInput(), self::$filterableAttributeTypes)) {
            return null;
        }

        if (!empty($attributeModel->getSwatchInputType())) {
            return self::$filterableAttributeTypes['swatch'];
        }

        return $attributeModel->getFrontendInput();
    }

    private function getFilterableAttributes($category)
    {
        if (empty($this->filterableAttributes)) {
            $this->filterableAttributes = $this->filterableAttributesProvider->getList($category);
        }

        return $this->filterableAttributes;
    }

    private function addFilterValueToUrlParameters($filter, $filterValue, $parameters)
    {
        $attributeCode = $filter->getRequestVar();
        $attributeType = $this->getAttributeType($filter);

        if (!isset($parameters[$attributeCode]) || !in_array($attributeType, self::$filterableAttributeTypes)) {
            $parameters[$attributeCode] = $filterValue;
            return $parameters;
        }

        if ($attributeType === self::ATTRIBUTE_TYPE_SELECT) {
            $parameters[$attributeCode] = null;
            return $parameters;
        }

        if (!is_array($parameters[$attributeCode])) {
            $parameters[$attributeCode] = [$parameters[$attributeCode]];
        }

        $optionPosition = array_search($filterValue, $parameters[$attributeCode]);

        if ($optionPosition === false) {
            $parameters[$attributeCode][] = $filterValue;
            return $parameters;
        }

        unset($parameters[$attributeCode][$optionPosition]);
        return $parameters;
    }

    private function removeFilterValueFromUrlParameters($filter, $filterValue, $parameters)
    {
        $attributeCode = $filter->getRequestVar();

        if (!isset($parameters[$attributeCode])) {
            return $parameters;
        }

        if (is_array($parameters[$attributeCode])) {
            $optionPosition = array_search($filterValue, $parameters[$attributeCode]);

            if ($optionPosition !== false) {
                unset($parameters[$attributeCode][$optionPosition]);
            }

            return $parameters;
        }

        $parameters[$attributeCode] = null;
        return $parameters;
    }
}
