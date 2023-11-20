<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterableAttributesProvider
{
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG = 'category_filter_attributes_%s_%s';

    protected \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\FilterableAttribute\Category\CollectionFactory $attributeCollectionFactory;
    protected \Smile\ElasticsuiteCore\Api\Search\ContextInterface $searchContext;
    protected \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory $fulltextCollectionFactory;
    protected \Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider $filterProvider;
    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;
    protected \Magento\Framework\App\CacheInterface $cache;
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper;
    protected \Magento\Framework\App\RequestInterface $request;
    protected \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    protected \Magento\Framework\App\State $state;

    public function __construct(
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\FilterableAttribute\Category\CollectionFactory $attributeCollectionFactory,
        \Smile\ElasticsuiteCore\Api\Search\ContextInterface $searchContext,
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory $fulltextCollectionFactory,
        \Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider $filterProvider,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\State $state
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchContext = $searchContext;
        $this->fulltextCollectionFactory = $fulltextCollectionFactory;
        $this->filterProvider = $filterProvider;
        $this->configuration = $configuration;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->categoryHelper = $categoryHelper;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->state = $state;
    }

    public function getList($currentCategory)
    {
        $currentCategory = $this->categoryHelper->getCategoryEntityForSearchResultPage($currentCategory);

        if ($currentCategory) {
            $categoryId = $currentCategory->getId();
        } else {
            $categoryId = $this->request->getParam('cat');
        }

        if (!$categoryId) {
            return [];
        }

        $cacheKey = $this->getCacheKey($categoryId);
        $cachedData = $this->cache->load($cacheKey);

        if (!empty($cachedData)) {
            return $this->serializer->unserialize($this->cache->load($cacheKey));
        }

        if (!$currentCategory) {
            $currentCategory = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
        }

        $attributesList = [];

        $filterLinkMasking = $currentCategory->getSeoLinkMasking();

        foreach ($this->getAttributes($currentCategory) as $attribute) {

            $attributeId = (int)$attribute->getAttributeId();
            $isMasked = $filterLinkMasking[$attributeId] ?? $this->configuration->getDefaultMaskingState();

            $attributesList[$attribute->getAttributeCode()] = [
                'attribute_id' => $attributeId,
                'attribute_label' => $attribute->getFrontendLabel(),
                'is_masked' => $isMasked
            ];
        }

        $this->cache->save($this->serializer->serialize($attributesList), $cacheKey, array_merge(['category_filter_attributes'], $currentCategory->getIdentities()), self::CACHE_LIFETIME);

        return $attributesList;
    }

    protected function getCacheKey($categoryId)
    {
        return sprintf(self::CACHE_TAG, $categoryId, $this->storeManager->getStore()->getId());
    }

    protected function getAttributes($category)
    {
        $collection = $this->attributeCollectionFactory->create(['category' => $category]);

        $collection
            ->setCategory($category)
            ->addIsFilterableFilter()
            ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT, ['in' => \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes])
            ->addStoreLabel($category->getStoreId())
            ->setOrder('position', \Magento\Framework\Api\SortOrder::SORT_ASC)
            ->setOrder('attribute_id', \Magento\Framework\Api\SortOrder::SORT_ASC);

        $storeId = $this->getStoreId($category);

        if ($storeId && $category->getId() && $this->state->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->searchContext
                ->setCurrentCategory($category)
                ->setStoreId($storeId);

            $fulltextCollection = $this->fulltextCollectionFactory->create();

            $fulltextCollection
                ->setStoreId($storeId)
                ->addFieldToFilter('category_ids', $this->filterProvider->getQueryFilter($category));

            $attributeSetIds = array_keys($fulltextCollection->getFacetedData('attribute_set_id'));

            if (!empty($attributeSetIds)) {
                $collection->setAttributeSetFilter($attributeSetIds);
            }
        }

        return $collection->getItems();
    }

    protected function getStoreId($category)
    {
        $storeId = $category->getStoreId();

        if (is_numeric($storeId)) {
            return $storeId;
        }

        $categoryStoreIds = array_filter($category->getStoreIds());

        return current($categoryStoreIds);
    }
}
