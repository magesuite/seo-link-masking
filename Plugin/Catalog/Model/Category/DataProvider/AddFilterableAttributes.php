<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Category\DataProvider;

class AddFilterableAttributes
{
    const DEFAULT_CATEGORY_LEVEL = 2;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider
     */
    protected $filterableAttributesProvider;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider $filterableAttributesProvider
    ) {
        $this->configuration = $configuration;
        $this->filterableAttributesProvider = $filterableAttributesProvider;
    }

    public function aroundGetMeta(\Magento\Catalog\Model\Category\DataProvider $dataProvider, \Closure $proceed)
    {
        $meta = $proceed();

        if (!$this->configuration->isLinkMaskingEnabled()) {
            $meta['search_engine_optimization']['children']['seo_link_masking']['arguments']['data']['config']['visible'] = false;
            return $meta;
        }

        $currentCategory = $dataProvider->getCurrentCategory();

        if ($currentCategory->getId() === null || $currentCategory->getLevel() < self::DEFAULT_CATEGORY_LEVEL) {
            $meta['search_engine_optimization']['children']['seo_link_masking']['arguments']['data']['config']['visible'] = false;
        }

        return $meta;
    }

    public function aroundGetData(\Magento\Catalog\Model\Category\DataProvider $dataProvider, \Closure $proceed)
    {
        $data = $proceed();

        if (!$this->configuration->isLinkMaskingEnabled()) {
            return $data;
        }

        $currentCategory = $dataProvider->getCurrentCategory();

        if ($currentCategory->getId() !== null && $currentCategory->getLevel() >= self::DEFAULT_CATEGORY_LEVEL) {
            $data[$currentCategory->getId()]['seo_link_masking'] = array_values($this->filterableAttributesProvider->getList($currentCategory));
        }

        return $data;
    }
}
