<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Service;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
 */
class FilterItemUrlProcessorTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Filter
     */
    protected $filterHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);

        $this->filterHelper = $this->createStub(
            \MageSuite\SeoLinkMasking\Helper\Filter::class
        );

        $this->objectManager->addSharedInstance($this->filterHelper, \MageSuite\SeoLinkMasking\Helper\Filter::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture loadFilterableProducts
     */
    public function testLinkMaskingGetCorrectCategoryUrl()
    {
        $this->dispatch('catalog/navigation_filter/ajax/id/333/?cat=333&filterName=multiselect_attribute');

        $response = json_decode($this->getResponse()->getBody(), true);

        $urlContainPath = strpos($response[0]['url'], 'http://localhost/index.php/main-category.html/option') !== false;

        $this->assertTrue($urlContainPath);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture loadFilterableProducts
     */
    public function testLinkMaskingGetCorrectCategoryUrlSearchResult()
    {
        $this->dispatch('catalog/navigation_filter/ajax/?filterName=multiselect_attribute');

        $response = json_decode($this->getResponse()->getBody(), true);

        $urlContainPath = strpos($response[0]['url'], 'http://localhost/index.php/catalogsearch/result/index/option') !== false;

        $this->assertTrue($urlContainPath);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture loadFilterableProducts
     */
    public function testItReturnsCorrectUrlForMaskedFilterInAjaxRequest()
    {
        $this->filterHelper->method('isFilterMasked')->willReturn(true);

        $this->dispatch('catalog/navigation_filter/ajax/?filterName=multiselect_attribute');

        $response = json_decode($this->getResponse()->getBody(), true);
        $decodedUrl = json_decode($response[0]['url'], true);

        $this->assertEquals('http://localhost/index.php/linkmasking/filter/redirect/', $decodedUrl['action']);

        $urlContainPath = strpos($decodedUrl['data']['url'], 'http://localhost/index.php/catalogsearch/result/index/option') !== false;
        $this->assertTrue($urlContainPath);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 0
     * @magentoDataFixture loadFilterableProducts
     */
    public function testItReturnsCorrectUrlFilterUrlIfMaskingIsEnabledButShortFilterUrlIdDisabledInAjaxRequest()
    {
        $this->filterHelper->method('isFilterMasked')->willReturn(true);

        $this->dispatch('catalog/navigation_filter/ajax/?filterName=multiselect_attribute');

        $response = json_decode($this->getResponse()->getBody(), true);
        $decodedUrl = json_decode($response[0]['url'], true);

        $this->assertEquals('http://localhost/index.php/linkmasking/filter/redirect/', $decodedUrl['action']);

        $urlContainPath = strpos($decodedUrl['data']['url'], 'http://localhost/index.php/?multiselect_attribute=Option') !== false;
        $this->assertTrue($urlContainPath);
    }

    public static function loadFilterableProducts()
    {
        require __DIR__.'/../_files/filterable_products.php';

        $indexerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Indexer\IndexerRegistry::class);
        $indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->reindexAll();
    }

    public static function loadFilterableProductsRollback()
    {
        require __DIR__.'/../_files/filterable_products_rollback.php';
    }
}
