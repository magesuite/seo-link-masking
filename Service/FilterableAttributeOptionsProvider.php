<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterableAttributeOptionsProvider
{
    public const CACHE_LIFETIME = 86400;
    public const CACHE_TAG = 'filter_attribute_options_%s';

    protected \Magento\Framework\App\CacheInterface $cache;
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory;
    protected \MageSuite\SeoLinkMasking\Helper\Url $urlHelper;

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

    public function getOptions($storeId = null): array
    {
        $cacheKey = $this->getCacheKey((int)$storeId);
        $cachedData = $this->cache->load($cacheKey);

        if (!empty($cachedData)) {
            return $this->serializer->unserialize($cachedData);
        }

        $options = [];

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, true)
            ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT, \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes);

        foreach ($attributeCollection as $attribute) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            if (!empty($storeId)) {
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

    public function getCacheKey(?int $storeId = null): string
    {
        if (empty($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return sprintf(self::CACHE_TAG, $storeId);
    }

    public function rewriteOption(\Magento\Framework\DataObject $parameterOptions): array
    {
        if (!is_array($parameterOptions->getValue())) {
            $parameterOptions->setValue([$parameterOptions->getValue()]);
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, true)
            ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT, \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes)
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::ATTRIBUTE_CODE, $parameterOptions->getCode())
            ->setPageSize(1);

        $attribute = $attributeCollection->getFirstItem();
        $attribute->setStoreId($parameterOptions->getOldStoreId());
        $options = [];

        foreach ($attribute->getOptions() as $option) {
            if (in_array($option->getLabel(), $parameterOptions->getValue())) {
                $options[] = $option->getValue();
            }
        }

        $attribute->setStoreId($parameterOptions->getTargetStoreId());
        $newValues = [];

        foreach ($attribute->getOptions() as $option) {
            if (in_array($option->getValue(), $options)) {
                $newValues[] = $option->getLabel();
            }
        }

        return $newValues;
    }
}
