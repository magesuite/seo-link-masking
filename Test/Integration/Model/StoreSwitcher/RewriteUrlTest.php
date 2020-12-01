<?php

namespace Magento\UrlRewrite\Model\StoreSwitcher;

/**
 * Class RewriteUrlTest
 * @package Magento\UrlRewrite\Model\StoreSwitcher
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RewriteUrlTest extends \PHPUnit\Framework\TestCase
{
    const CATEGORY_WITH_LINK_MASKING = 778;

    /**
     * @var \Magento\Store\Model\StoreSwitcher
     */
    private $storeSwitcher;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Class dependencies initialization
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->storeSwitcher = $this->objectManager->get(\Magento\Store\Model\StoreSwitcher::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture loadFilterableProductsMultistore
     * @return void
     */
    public function testSwitchToExistingPage(): void
    {
        $fromStoreCode = 'default';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $toStore = $storeRepository->get($toStoreCode);

        $redirectUrl = "http://localhost/index.php/test-category/option+1";
        $expectedUrl = "http://localhost/index.php/test-category-translated/option+1+translated";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }

    public static function loadFilterableProductsMultistore()
    {
        require __DIR__.'/../../_files/filterable_products_multistore.php';
    }

    public static function loadFilterableProductsMultistoreRollback()
    {
        require __DIR__.'/../../_files/filterable_products_multistore_rollback.php';
    }
}
