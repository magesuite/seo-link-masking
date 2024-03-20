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

        if (empty($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $storeId = (int)$storeId;

        $pathPartsCombinations = $this->getPathPartsCombinations($pathParts);
        $rewrites = $this->getRewrites($pathPartsCombinations, $storeId);

        if (empty($rewrites)) {
            return null;
        }

        foreach ($pathPartsCombinations as $path) {
            $rewrite = $this->findPathInReturnedRewrites($rewrites, $path);

            if ($rewrite == null) {
                continue;
            }

            return $rewrite;
        }

        return null;
    }

    protected function getRewrites(array $pathParts, int $storeId): array
    {
        return $this->urlFinder->findAllByData([
            'request_path' => $pathParts,
            'store_id' => $storeId
        ]);
    }

    protected function getPathPartsCombinations(array $pathParts): array
    {
        $pathPartsCombinations = [];

        foreach ($pathParts as $pathPart) {
            if (empty($pathParts)) {
                continue;
            }

            $pathPartsCombinations[] = implode('/', $pathParts);
            array_pop($pathParts);
        }

        return $pathPartsCombinations;
    }

    protected function findPathInReturnedRewrites(array $rewrites, string $path): ?\Magento\UrlRewrite\Service\V1\Data\UrlRewrite
    {
        foreach ($rewrites as $rewrite) {
            if ($rewrite->getRequestPath() == $path) {
                return $rewrite;
            }
        }

        return null;
    }
}
