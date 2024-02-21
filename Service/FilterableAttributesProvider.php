<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterableAttributesProvider
{
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG = 'category_filter_attributes_%s_%s';

    protected \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\FilterableAttribute\Category\CollectionFactory $attributeCollectionFactory;
    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;
    protected \Magento\Framework\App\CacheInterface $cache;
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper;
    protected \Magento\Framework\App\RequestInterface $request;
    protected \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\FilterableAttribute\Category\CollectionFactory $attributeCollectionFactory,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->configuration = $configuration;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->categoryHelper = $categoryHelper;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
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

        return $collection->getItems();
    }
}
