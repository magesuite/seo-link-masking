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
    public function aroundAddRemotePageAsset(\Magento\Framework\View\Page\Config $subject, callable $proceed, $url, $contentType, array $properties = [], $name = null)
    {
        if (empty($url)) {
            return $proceed($url, $contentType, $properties, $name);
        }

        if ($this->request->getFullActionName() === self::CATALOG_CATEGORY_VIEW_LAYOUT_HANDLE
            && $contentType === 'canonical'
            && $this->configurationHelper->areFilterParamsInCanonicalEnabled()) {
            $url = urldecode($this->getCurrentUrlWithoutParams());
            $url = str_replace(' ', '+', $url);
            return $proceed($url, $contentType, $properties, $name);
        } else {
            return $proceed($url, $contentType, $properties, $name);
        }
    }

    protected function getCurrentUrlWithoutParams()
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parsedUrl = parse_url($this->urlBuilder->getCurrentUrl());
        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
    }
}
