<?php

namespace MageSuite\SeoLinkMasking\Service\MetaRobotsTag;

class FilterStateValidator
{
    const CATEGORY_VIEW_FULL_ACTION_NAME = 'catalog_category_view';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider
     */
    protected $filterableAttributesProvider;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider $filterableAttributesProvider
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->configuration = $configuration;
        $this->filterableAttributesProvider = $filterableAttributesProvider;
    }

    public function isMaskedFilterSelected()
    {
        if ($this->request->getFullActionName() != self::CATEGORY_VIEW_FULL_ACTION_NAME) {
            return false;
        }

        $parameters = $this->request->getQueryValue();

        if (empty($parameters)) {
            return false;
        }

        $currentCategory = $this->registry->registry('current_category');

        if ($currentCategory == null) {
            return false;
        }

        $onlyOneFilterDamasked = $this->configuration->onlyOneFilterDemasked();

        $attributes = $this->filterableAttributesProvider->getList($currentCategory);

        $filterAttributesCount = 0;

        foreach ($parameters as $attributeCode => $parameter) {
            if (!isset($attributes[$attributeCode])) {
                continue;
            }

            $filterAttributesCount++;

            if ($attributes[$attributeCode]['is_masked']) {
                return true;
            }

            if ($onlyOneFilterDamasked && is_array($parameter)) {
                return true;
            }
        }

        if ($onlyOneFilterDamasked && $filterAttributesCount > 1) {
            return true;
        }

        return false;
    }
}
