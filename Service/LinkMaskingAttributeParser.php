<?php

namespace MageSuite\SeoLinkMasking\Service;

class LinkMaskingAttributeParser
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    public function __construct(\Magento\Framework\Serialize\SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function convertAttributeBeforeSave($filterLinkMasking)
    {
        if (!is_array($filterLinkMasking)) {
            return null;
        }

        $filterData = [];

        foreach ($filterLinkMasking as $attribute) {
            if (!isset($attribute['attribute_id']) || !isset($attribute['is_masked'])) {
                continue;
            }
            $filterData[$attribute['attribute_id']] = filter_var($attribute['is_masked'], FILTER_VALIDATE_BOOLEAN);
        }

        if (empty($filterData)) {
            return null;
        }

        return $this->serializer->serialize($filterData);
    }

    public function convertAttributeAfterLoad($filterLinkMasking)
    {
        $attributes = $this->serializer->unserialize($filterLinkMasking);

        $filterData = [];

        foreach ($attributes as $attributeId => $isMasked) {
            $filterData[$attributeId] = $isMasked;
        }

        return $filterData;
    }
}
