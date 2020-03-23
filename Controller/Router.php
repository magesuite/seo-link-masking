<?php

namespace MageSuite\SeoLinkMasking\Controller;

class Router implements \Magento\Framework\App\RouterInterface
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

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\SeoLinkMasking\Model\FilterParametersProcessor
     */
    protected $filterParametersProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Model\FilterParametersProcessor $filterParametersProcessor,
        \Magento\Framework\Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        $this->actionFactory = $actionFactory;
        $this->configuration = $configuration;
        $this->filterParametersProcessor = $filterParametersProcessor;
        $this->registry = $registry;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->configuration->isShortFilterUrlEnabled()) {
            return null;
        }

        $requestedUrl = ltrim($request->getPathInfo(), '/');
        $rewrite = $this->findRewrite($requestedUrl);

        if (empty($rewrite)) {
            return null;
        }

        $filterParams = substr($requestedUrl, strlen($rewrite->getRequestPath()));
        $registeredFilterParams = $this->registry->registry(\MageSuite\SeoLinkMasking\Helper\Configuration::LINK_MASKING_PARAMETER_REGISTRY_KEY);

        if (empty($filterParams) || !empty($registeredFilterParams)) {
            $this->registry->unregister(\MageSuite\SeoLinkMasking\Helper\Configuration::LINK_MASKING_PARAMETER_REGISTRY_KEY);
            return null;
        }

        $this->registry->register(\MageSuite\SeoLinkMasking\Helper\Configuration::LINK_MASKING_PARAMETER_REGISTRY_KEY, $filterParams);

        $processResult = $this->processUrlParameters($request, $filterParams);

        if (!$processResult) {
            return null;
        }

        if ($rewrite->getRedirectType()) {
             return null;
        }

        $request->setAlias(
            \Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
            $rewrite->getRequestPath()
        );

        $request->setPathInfo('/' . $rewrite->getTargetPath());
        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class
        );
    }

    protected function findRewrite($pathInfo)
    {
        $rewrite = $this->getSearchPageRewrite($pathInfo);
        return $rewrite ?? $this->findCategoryRewrite($pathInfo);
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

    protected function findCategoryRewrite($pathInfo)
    {
        $pathParts = explode('/', $pathInfo);

        $storeId = $this->storeManager->getStore()->getId();
        $rewrite = null;

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

    protected function processUrlParameters($request, $params)
    {
        $params = ltrim($params, '/');
        $params = explode('/', $params);

        $filterParameters = $this->filterParametersProcessor->process($params);

        if (count($params) != count($filterParameters)) {
            return false;
        }

        $request->setQueryValue($filterParameters);
        return true;
    }
}
