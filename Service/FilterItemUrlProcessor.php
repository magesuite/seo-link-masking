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

    const PAGE_PARAMETER = 'p';

    const URL_PARAMS_DELIMITER = '?';
    const PATH_SEPARATOR = '/';

    const MODE_ADD = 'add';
    const MODE_REMOVE = 'remove';

    const CATEGORY_URL_CACHE_TAG = 'category_url_%s_%s';

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

    /**
     * @var array
     */
    protected $filterableAttributes = [];

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $url,
        \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider $filterableAttributesProvider,
        \MageSuite\SeoLinkMasking\Service\FiltrableAttributeUtfFriendlyConverter $filtrableAttributeUtfFriendlyConverter,
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->filterableAttributesProvider = $filterableAttributesProvider;
        $this->filtrableAttributeUtfFriendlyConverter = $filtrableAttributeUtfFriendlyConverter;
        $this->urlHelper = $urlHelper;
        $this->configuration = $configuration;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
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

        return $this->buildFilterUrl($requestParameters, $filtersValues, $category);
    }

    protected function buildFilterUrl($requestParameters, $filtersValues, $category)
    {
        $requestParameters = $this->removePageParameter($requestParameters);

        $url = $this->getUrl($category, $requestParameters);

        if (empty($filtersValues)) {
            return $url;
        }

        $urlParts = explode(self::URL_PARAMS_DELIMITER, $url);

        if ($this->configuration->isUtfFriendlyModeEnabled()) {
            $filtersValues = $this->filtrableAttributeUtfFriendlyConverter->convertFilterParams($filtersValues);
        }

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

    protected function removePageParameter($params)
    {
        if (isset($params[self::PAGE_PARAMETER])) {
            $params[self::PAGE_PARAMETER] = null;
        }

        return $params;
    }

    public function getCategoryUrl($category)
    {
        if ($category) {
            $categoryId = $category->getId();
        } else {
            $categoryId = $this->request->getParam('cat');
        }

        $categoryUrlCacheKey = $this->getCategoryUrlCacheKey($categoryId);
        $categoryUrlCacheData = $this->cache->load($categoryUrlCacheKey);

        if ($categoryUrlCacheData) {
            return $categoryUrlCacheData;
        }

        if (!$category) {
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
        }

        $this->cache->save($category->getUrl(), $categoryUrlCacheKey, [sprintf('%s_%s', \Magento\Catalog\Model\Category::CACHE_TAG, $categoryId)]);

        return $category->getUrl();
    }

    public function getCategoryUrlCacheKey($categoryId)
    {
        return sprintf(self::CATEGORY_URL_CACHE_TAG, $categoryId, $this->storeManager->getStore()->getId());
    }

    public function getUrl($category, $requestParameters)
    {
        if ($this->request->getFullActionName() != \MageSuite\SeoLinkMasking\Helper\Configuration::AJAX_FILTER_FULL_ACTION_NAME) {
            return $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $requestParameters]);
        }

        if ($this->request->getParam('cat')) {
            return $this->getCategoryUrl($category);
        }

        $params = isset($requestParameters['q']) ? ['q' => $requestParameters['q']] : [];
        return $this->url->getUrl('catalogsearch/result/index', ['_current' => false, '_use_rewrite' => true, '_query' => $params]);
    }
}
