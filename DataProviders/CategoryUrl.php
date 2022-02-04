<?php

namespace MageSuite\SeoLinkMasking\DataProviders;

class CategoryUrl extends \MageSuite\Opengraph\DataProviders\TagProvider implements \MageSuite\Opengraph\DataProviders\TagProviderInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\Opengraph\Factory\TagFactoryInterface
     */
    protected $tagFactory;

    protected $tags = [];

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Opengraph\Factory\TagFactoryInterface $tagFactory
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->tagFactory = $tagFactory;
    }

    public function getTags()
    {
        $this->addUrlTag();

        return $this->tags;
    }

    protected function addUrlTag()
    {
        $urlRewrite = $this->urlFinder->findOneByData([
            'target_path' => trim($this->request->getPathInfo(), '/'),
            'store_id' => $this->storeManager->getStore()->getId()
        ]);

        if (!$urlRewrite) {
            return;
        }

        $currentUrl = $this->storeManager->getStore()->getBaseUrl() . $urlRewrite->getRequestPath();

        $linkMaskingParameters = $this->registry->registry(\MageSuite\SeoLinkMasking\Helper\Configuration::LINK_MASKING_PARAMETER_REGISTRY_KEY);

        if (!empty($linkMaskingParameters)) {
            $currentUrl .= $linkMaskingParameters;
        }

        $tag = $this->tagFactory->getTag('url', $currentUrl);

        $this->addTag($tag);
    }
}
