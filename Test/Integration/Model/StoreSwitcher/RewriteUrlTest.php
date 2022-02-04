<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Model\StoreSwitcher;

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
    protected $storeSwitcher;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * Class dependencies initialization
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->storeSwitcher = $objectManager->get(\Magento\Store\Model\StoreSwitcher::class);
        $this->storeRepository = $objectManager->get(\Magento\Store\Api\StoreRepositoryInterface::class);
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
        $fromStore = $this->storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        $toStore = $this->storeRepository->get($toStoreCode);

        $redirectUrl = "http://localhost/index.php/test-category/option+1";
        $expectedUrl = "http://localhost/index.php/test-category-translated/option+1+translated";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/seo/category_url_suffix
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/two_categories_multistore.php
     * @return void
     */
    public function testSwitchToProperCategoryPage(): void
    {
        $fromStore = $this->storeRepository->get('default');
        $toStore = $this->storeRepository->get('fixture_second_store');
        $expectedUrl = "http://localhost/index.php/category/subcategory-fixturestore.html";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $expectedUrl));
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
