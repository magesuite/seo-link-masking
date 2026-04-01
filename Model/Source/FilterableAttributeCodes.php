<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Model\Source;

class FilterableAttributeCodes implements \Magento\Framework\Option\ArrayInterface
{
    protected array $options = [];

    public function __construct(
        protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        if (!empty($this->options)) {
            return $this->options;
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->addFieldToFilter(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, true)
            ->addFieldToFilter(
                \Magento\Eav\Api\Data\AttributeInterface::FRONTEND_INPUT,
                \MageSuite\SeoLinkMasking\Service\FilterItemUrlProcessor::$filterableAttributeTypes
            )
            ->setOrder(\Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_CODE, 'ASC');

        foreach ($attributeCollection as $attribute) {
            $code = $attribute->getAttributeCode();
            $this->options[] = ['value' => $code, 'label' => $code];
        }

        return $this->options;
    }
}
