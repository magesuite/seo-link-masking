<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Controller\Adminhtml\Attribute;

class CleanCache extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_SeoLinkMasking::configuration';

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Framework\App\CacheInterface $cache;
    protected \MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider $filterableAttributeOptionsProvider;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\CacheInterface $cache,
        \MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider $filterableAttributeOptionsProvider
    ) {
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->filterableAttributeOptionsProvider = $filterableAttributeOptionsProvider;

        parent::__construct($context);
    }

    public function execute()
    {
        $this->cleanCache();

        $this->messageManager->addSuccessMessage(__('Attribute options cache has been cleaned.'));

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    protected function cleanCache(): void
    {
        $stores = $this->storeManager->getStores(true);

        foreach ($stores as $store) {
            $cacheKey = $this->filterableAttributeOptionsProvider->getCacheKey((int)$store->getId());
            $this->cache->remove($cacheKey);
        }
    }
}
