<?php

namespace MageSuite\SeoLinkMasking\Service;

class UrlRewriteFinder
{
    const SEARCH_PAGE_URL_PARAMS = ['catalogsearch', 'result', 'index'];
    const BRANDS_PAGE_URL_PARAMS = ['brands', 'index', 'index'];

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder;

    protected \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        $this->request = $request;
    }

    public function findRewrite($pathInfo, $storeId = null)
    {
        $rewrite = $this->getSearchPageRewrite($pathInfo);

        if ($rewrite) {
            return $rewrite;
        }

        $rewrite = $this->getBrandPageRewrite($pathInfo);

        if ($rewrite) {
            return $rewrite;
        }

        return $this->findCategoryRewrite($pathInfo, $storeId);
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

    protected function getBrandPageRewrite($pathInfo)
    {
        if ($this->request->getModuleName() != self::BRANDS_PAGE_URL_PARAMS[0] ||
            $this->request->getControllerName() != self::BRANDS_PAGE_URL_PARAMS[1] ||
            $this->request->getActionName() != self::BRANDS_PAGE_URL_PARAMS[2]
        ) {
            return null;
        }

        $pathInfo = rtrim($pathInfo, '/');
        $pathParts = explode('/', $pathInfo);

        $cleanRequestPath = implode('/', array_slice($pathParts, 0, 2));

        return new \Magento\Framework\DataObject([
            'request_path' => $cleanRequestPath,
            'target_path' => $cleanRequestPath,
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
