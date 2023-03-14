<?php

namespace MageSuite\SeoLinkMasking\Service;

class DuplicatedOptionsNotifier
{
    protected ?array $restrictedAttributesList = null;

    public const CACHE_TAG = 'filter_attribute_duplicated_options';

    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;

    protected \Magento\Framework\App\CacheInterface $cache;

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory;

    protected \MageSuite\SeoLinkMasking\Helper\Url $url;

    protected ?array $uniqueAttributesForOption = null;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \MageSuite\SeoLinkMasking\Helper\Url $url
    ) {
        $this->configuration = $configuration;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->url = $url;
    }

    public function restrictAttributesCheckToGivenList(array $restrictedAttributesList): void
    {
        $this->restrictedAttributesList = $restrictedAttributesList;
    }

    public function isWarningDisplayed(): bool
    {
        return (bool)count($this->getDuplicatedOptionsInAttributes());
    }

    public function getDuplicatedOptionsInAttributes(): array
    {
        if ($this->uniqueAttributesForOption === null) {
            $options = $this->getOptions();
            $duplicatedOptionsInAttributes = $this->getAttributesWithDuplicates($options);
            $this->uniqueAttributesForOption = $this->getUniqueAttributesForOption($duplicatedOptionsInAttributes);
        }

        return $this->uniqueAttributesForOption;
    }

    protected function getOptions(): array
    {
        $cachedData = $this->cache->load(self::CACHE_TAG);
        if (!empty($cachedData)) {
            return $this->serializer->unserialize($cachedData);
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, true)
            ->addFieldToFilter(
                \Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT,
                \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes
            );

        $options = [];

        foreach ($attributeCollection as $attribute) {

            if (isset($this->restrictAttributesToGivenList) &&
                !in_array($attribute->getAttributeCode(), $this->restrictAttributesToGivenList)
            ) {
                continue;
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            foreach ($attribute->getOptions() as $option) {
                if (!$option->getValue()) {
                    continue;
                }
                $key = $this->url->encodeValue($option->getLabel());
                $options[$key][] = $attribute->getAttributeCode();
            }
        }

        $this->cache->save(
            $this->serializer->serialize($options),
            self::CACHE_TAG,
            [],
            $this->configuration->getCacheLengthForWarningAboutDuplicatedOptions()
        );
        return $options;
    }

    protected function getAttributesWithDuplicates(array $values): array
    {
        foreach ($values as $option => $attributes) {

            if (count($attributes) > 1) {
                continue;
            }
            unset($values[$option]);

        }
        return $values;
    }

    protected function getUniqueAttributesForOption(array $values): array
    {
        foreach ($values as $option => $attributes) {
               $values[$option] = array_unique($attributes);
        }
        return $values;
    }
}
