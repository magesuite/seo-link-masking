<?php

namespace MageSuite\SeoLinkMasking\Plugin\Catalog\Block\Category\View;

class AddFilterParamsToCanonicalLink
{
    const CATALOG_CATEGORY_VIEW_LAYOUT_HANDLE = 'catalog_category_view';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configurationHelper;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configurationHelper
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->configurationHelper = $configurationHelper;
    }

    public function aroundAddRemotePageAsset(\Magento\Framework\View\Page\Config $subject, callable $proceed, ...$args)
    {
        if (!isset($args[1])) {
            $proceed(...$args);
        }

        if ($this->request->getFullActionName() === self::CATALOG_CATEGORY_VIEW_LAYOUT_HANDLE && $args[1] === 'canonical' && $this->configurationHelper->isEnableFilterParamsInCanonical()) {
            $args[0] = urldecode($this->getCurrentUrlWithoutParams());
            $args[0] = str_replace(' ', '+', $args[0]);
            $proceed(...$args);
        } else {
            $proceed(...$args);
        }
    }

    protected function getCurrentUrlWithoutParams()
    {
        $parsedUrl = parse_url($this->urlBuilder->getCurrentUrl());
        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
    }
}
