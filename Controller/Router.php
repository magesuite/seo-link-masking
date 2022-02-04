<?php

namespace MageSuite\SeoLinkMasking\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \MageSuite\SeoLinkMasking\Service\UrlRewriteFinder
     */
    protected $urlRewriteFinder;

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
        \MageSuite\SeoLinkMasking\Service\UrlRewriteFinder $urlRewriteFinder,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Model\FilterParametersProcessor $filterParametersProcessor,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlRewriteFinder = $urlRewriteFinder;
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

        $requestedUrl = trim($request->getPathInfo(), '/');
        $rewrite = $this->urlRewriteFinder->findRewrite($requestedUrl);

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

    protected function processUrlParameters($request, $params)
    {
        $filterParameters = $this->filterParametersProcessor->process($params);

        if (empty($filterParameters)) {
            return false;
        }

        $request->setQueryValue($filterParameters);

        return true;
    }
}
