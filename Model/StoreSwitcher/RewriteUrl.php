<?php

namespace MageSuite\SeoLinkMasking\Model\StoreSwitcher;

/**
 * Handle url rewrites for redirect url with seo link masking paramters
 */
class RewriteUrl implements \Magento\Store\Model\StoreSwitcherInterface
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\UrlRewriteFinder
     */
    protected $urlRewriteFinder;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \MageSuite\SeoLinkMasking\Model\FilterParametersProcessor
     */
    protected $filterParametersProcessor;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Url
     */
    protected $urlHelper;

    public function __construct(
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory,
        \MageSuite\SeoLinkMasking\Service\UrlRewriteFinder $urlRewriteFinder,
        \MageSuite\SeoLinkMasking\Model\FilterParametersProcessor $filterParametersProcessor,
        \MageSuite\SeoLinkMasking\Helper\Url $urlHelper
    ) {
        $this->urlFinder = $urlFinder;
        $this->requestFactory = $requestFactory;
        $this->urlRewriteFinder = $urlRewriteFinder;
        $this->filterParametersProcessor = $filterParametersProcessor;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Switch to another store.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $fromStore
     * @param \Magento\Store\Api\Data\StoreInterface $targetStore
     * @param string $redirectUrl
     * @return string
     */
    public function switch(
        \Magento\Store\Api\Data\StoreInterface $fromStore,
        \Magento\Store\Api\Data\StoreInterface $targetStore,
        string $redirectUrl
    ): string {
        $targetUrl = $redirectUrl;
        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->requestFactory->create(['uri' => $targetUrl]);
        $urlPath = ltrim($request->getPathInfo(), '/');

        if ($targetStore->isUseStoreInUrl()) {
            // Remove store code in redirect url for correct rewrite search
            $storeCode = preg_quote($targetStore->getCode() . '/', '/');
            $pattern = "@^($storeCode)@";
            $urlPath = preg_replace($pattern, '', $urlPath);
        }

        $urlRewrite = $this->urlFinder->findOneByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REQUEST_PATH => $urlPath,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $targetStore->getId(),
        ]);

        if ($urlRewrite) {
            return $targetUrl;
        }

        $oldStoreId = $fromStore->getId();
        $oldRewrite = $this->urlRewriteFinder->findRewrite($urlPath, $oldStoreId);

        if ($oldRewrite) {
            $targetUrl = $targetStore->getBaseUrl();
            // look for url rewrite match on the target store
            $currentRewrite = $this->urlFinder->findOneByData([
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::TARGET_PATH => $oldRewrite->getTargetPath(),
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $targetStore->getId(),
            ]);
            if ($currentRewrite) {
                $targetUrl .= $currentRewrite->getRequestPath();
            } else {
                return $redirectUrl;
            }

            $filterParams = substr($urlPath, strlen($oldRewrite->getRequestPath()));
            $processResult = $this->processUrlParameters($filterParams, $oldStoreId, $targetStore->getId());
            if ($processResult) {
                $targetUrl .= $processResult;
            }
        }

        return $targetUrl;
    }

    protected function processUrlParameters($params, $oldStoreId, $targetStoreId)
    {
        $filterParameters = $this->filterParametersProcessor->processRewrite($params, $oldStoreId, $targetStoreId);

        if (empty($filterParameters)) {
            return false;
        }

        return $this->filterParametersProcessor->toUrl($filterParameters);
    }
}
