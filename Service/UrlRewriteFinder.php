<?php

namespace MageSuite\SeoLinkMasking\Service;

class UrlRewriteFinder
{
    const SEARCH_PAGE_URL_PARAMS = ['catalogsearch', 'result', 'index'];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
    ) {
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
    }

    public function findRewrite($pathInfo, $storeId = null)
    {
        $rewrite = $this->getSearchPageRewrite($pathInfo);
        return $rewrite ?? $this->findCategoryRewrite($pathInfo, $storeId);
    }

    protected function getSearchPageRewrite($pathInfo)
    {
        if (strpos($pathInfo, self::SEARCH_PAGE_URL_PARAMS[0]) === false) {
            return null;
        }

        $pathInfo = rtrim($pathInfo, '/');
        $pathParts = explode('/', $pathInfo);

        $searchPageUrlParams = array_intersect(self::SEARCH_PAGE_URL_PARAMS, $pathParts);
        $searchPageUrl = implode('/', $searchPageUrlParams);

        return new \Magento\Framework\DataObject([
            'request_path' => $searchPageUrl,
            'target_path' => $searchPageUrl,
            'redirect_type' => null
        ]);
    }

    protected function findCategoryRewrite($pathInfo, $storeId)
    {
        $pathParts = explode('/', $pathInfo);
        $rewrite = null;
        if (empty($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        while (empty($rewrite) && !empty($pathParts)) {
            $rewrite = $this->getRewrite(implode('/', $pathParts), $storeId);
            array_pop($pathParts);
        }

        return $rewrite;
    }

    protected function getRewrite($requestPath, $storeId)
    {
        return $this->urlFinder->findOneByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REQUEST_PATH => $requestPath,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $storeId,
        ]);
    }
}
