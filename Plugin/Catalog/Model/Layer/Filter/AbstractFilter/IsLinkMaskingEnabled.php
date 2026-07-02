<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\AbstractFilter;

class IsLinkMaskingEnabled
{
    public function __construct(
        protected \Magento\Framework\App\RequestInterface $request,
        protected \Magento\Framework\Registry $registry,
        protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        protected \MageSuite\SeoLinkMasking\Helper\Filter $filterHelper,
        protected \MageSuite\SeoLinkMasking\Helper\Category $categoryHelper
    ) {
    }

    public function aroundGetData(
        \Magento\Catalog\Model\Layer\Filter\AbstractFilter $subject,
        \Closure $proceed,
        $key = '',
        $index = null
    ): mixed {
        if ($key != 'is_link_masking_enabled' || !$subject->hasAttributeModel()) {
            return $proceed($key, $index);
        }

        $category = $this->getCategory($subject);

        if (empty($category)) {
            return $proceed($key, $index);
        }

        $attributeId = $subject->getAttributeModel()->getId();

        return $this->filterHelper->isFilterMasked($category, $attributeId);
    }

    protected function getCategory(
        \Magento\Catalog\Model\Layer\Filter\AbstractFilter $subject
    ): ?\Magento\Catalog\Api\Data\CategoryInterface {
        $isAjaxFilterCall = $this->request->getFullActionName() == \MageSuite\SeoLinkMasking\Helper\Page::AJAX_FILTER_FULL_ACTION_NAME;

        if ($isAjaxFilterCall && $this->isSearchContext()) {
            return $this->categoryHelper->getRootCategory();
        }

        if ($isAjaxFilterCall) {
            return $subject->getLayer()->getCurrentCategory();
        }

        $category = $this->registry->registry('current_category');

        return $this->categoryHelper->getCategoryEntityForSearchResultPage($category);
    }

    protected function isSearchContext(): bool
    {
        return $this->request->getParam('q') !== null;
    }
}
