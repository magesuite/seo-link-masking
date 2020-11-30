<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterableAttributeOptionsProvider
{
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG = 'filter_attribute_options_%s';

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Url
     */
    protected $urlHelper;

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->urlHelper = $urlHelper;
    }

    public function getOptions($storeId = null)
    {
        $cacheKey = $this->getCacheKey($storeId);
        $cachedData = $this->cache->load($cacheKey);

        if (!empty($cachedData)) {
            return $this->serializer->unserialize($this->cache->load($cacheKey));
        }

        $options = [];

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, true)
            ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT, \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes);

        foreach ($attributeCollection as $attribute) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            if(!empty($storeId)) {
                $attribute->setStoreId($storeId);
            }

            $code = $attribute->getAttributeCode();
            $type = $attribute->getFrontendInput();

            foreach ($attribute->getOptions() as $option) {
                if (!$option->getValue()) {
                    continue;
                }

                $key = $this->urlHelper->encodeValue($option->getLabel());
                $options[$key] = [
                    'code' => $code,
                    'type' => $type,
                    'value' => $option->getLabel()
                ];
            }
        }

        $this->cache->save($this->serializer->serialize($options), $cacheKey, [], self::CACHE_LIFETIME);

        return $options;
    }

    private function getCacheKey($storeId = null)
    {
        if(empty($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return sprintf(self::CACHE_TAG, $storeId);
    }

    public function rewriteOption($code, $oldValues, $oldStoreId, $targetStoreId){
        if(!is_array($oldValues)){
            $oldValues = [$oldValues];
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, true)
            ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT, \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes)
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::ATTRIBUTE_CODE, $code)
            ->setPageSize(1);

        $attribute = $attributeCollection->getFirstItem();
        $attribute->setStoreId($oldStoreId);
        $options = [];

        foreach ($attribute->getOptions() as $option) {
            if(in_array($option->getLabel(), $oldValues)){
                $options[] = $option->getValue();
            }
        }

        $attribute->setStoreId($targetStoreId);
        $newValues = [];

        foreach ($attribute->getOptions() as $option) {
            if(in_array($option->getValue(), $options)){
                $newValues[] = $option->getLabel();
            }
        }

        return $newValues;
    }
}
