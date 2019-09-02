<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Category;

class AddLinkMaskingToCategory
{
    /**
     * @var \MageSuite\SeoLinkMasking\Service\LinkMaskingAttributeParser
     */
    protected $linkMaskingAttributeParser;

    public function __construct(\MageSuite\SeoLinkMasking\Service\LinkMaskingAttributeParser $linkMaskingAttributeParser)
    {
        $this->linkMaskingAttributeParser = $linkMaskingAttributeParser;
    }

    public function beforeSave(\Magento\Catalog\Model\Category $subject)
    {
        $filterLinkMasking = $subject->getSeoLinkMasking();

        if (empty($filterLinkMasking)) {
            return [];
        }

        $parsedData = $this->linkMaskingAttributeParser->convertAttributeBeforeSave($filterLinkMasking);

        if (!empty($parsedData)) {
            $subject->setSeoLinkMasking($parsedData);
        }

        return [];
    }

    public function afterLoad(\Magento\Catalog\Model\Category $subject, $result)
    {
        $filterLinkMasking = $result->getSeoLinkMasking();

        if (empty($filterLinkMasking)) {
            return $result;
        }

        $subject->setSeoLinkMasking($this->linkMaskingAttributeParser->convertAttributeAfterLoad($filterLinkMasking));

        return $result;
    }
}
