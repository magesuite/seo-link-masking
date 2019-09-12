<?php

namespace MageSuite\SeoLinkMasking\Service;

class FilterableAttributeOptionsProvider
{
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG = 'filter_attribute_options';

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

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
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->urlHelper = $urlHelper;
    }

    public function getOptions()
    {
        $cacheKey = $this->getCacheKey();
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
            $code = $attribute->getAttributeCode();
            $type = $attribute->getFrontendInput();

            foreach ($attribute->getOptions() as $option) {
                if (!$option->getValue()) {
                    continue;
                }

                $key = $this->urlHelper->encodeValue($option->getLabel());
                $options[$key] = [
                    'code' => $code,
                    'type' => $type
                ];
            }
        }

        $this->cache->save($this->serializer->serialize($options), $cacheKey, [], self::CACHE_LIFETIME);

        return $options;
    }

    private function getCacheKey()
    {
        return self::CACHE_TAG;
    }
}
