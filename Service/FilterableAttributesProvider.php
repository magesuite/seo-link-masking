<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterableAttributesProvider
{
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG = 'category_filter_attributes_%s';

    /**
     * @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\FilterableAttribute\Category\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Search\ContextInterface
     */
    protected $searchContext;

    /**
     * @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory
     */
    protected $fulltextCollectionFactory;

    /**
     * @var \Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider
     */
    protected $filterProvider;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    public function __construct(
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\FilterableAttribute\Category\CollectionFactory $attributeCollectionFactory,
        \Smile\ElasticsuiteCore\Api\Search\ContextInterface $searchContext,
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory $fulltextCollectionFactory,
        \Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider $filterProvider,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchContext = $searchContext;
        $this->fulltextCollectionFactory = $fulltextCollectionFactory;
        $this->filterProvider = $filterProvider;
        $this->configuration = $configuration;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function getList($currentCategory)
    {
        $cacheKey = $this->getCacheKey($currentCategory->getId());

        $attributesList = $this->serializer->unserialize($this->cache->load($cacheKey));

        if (!empty($attributesList)) {
            return $attributesList;
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

        $this->cache->save($this->serializer->serialize($attributesList), $cacheKey, ['category_filter_attributes'], self::CACHE_LIFETIME);

        return $attributesList;
    }

    private function getCacheKey($categoryId)
    {
        return sprintf(self::CACHE_TAG, $categoryId);
    }

    private function getAttributes($category)
    {
        $collection = $this->attributeCollectionFactory->create(['category' => $category]);

        $collection
            ->setCategory($category)
            ->addIsFilterableFilter()
            ->addStoreLabel($category->getStoreId())
            ->setOrder('position', \Magento\Framework\Api\SortOrder::SORT_ASC)
            ->setOrder('attribute_id', \Magento\Framework\Api\SortOrder::SORT_ASC);

        $storeId = $this->getStoreId($category);

        if ($storeId && $category->getId()) {
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

    private function getStoreId($category)
    {
        $storeId = $category->getStoreId();

        if ($storeId) {
            return $storeId;
        }

        $categoryStoreIds = array_filter($category->getStoreIds());

        return current($categoryStoreIds);
    }
}
