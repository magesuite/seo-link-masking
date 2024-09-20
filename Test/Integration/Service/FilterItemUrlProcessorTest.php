<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Service;

class FilterItemUrlProcessorTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    protected ?\MageSuite\SeoLinkMasking\Helper\Filter $filterHelper;
    protected ?\Magento\Framework\Module\Manager $moduleManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->_objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->moduleManager = $this->_objectManager->get(\Magento\Framework\Module\Manager::class);
        $this->filterHelper = $this->createStub(
            \MageSuite\SeoLinkMasking\Helper\Filter::class
        );
        $this->_objectManager->addSharedInstance($this->filterHelper, \MageSuite\SeoLinkMasking\Helper\Filter::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
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
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
     */
    public function testLinkMaskingGetCorrectCategoryUrlWithNonAjaxRequest()
    {
        $this->dispatch('http://localhost/index.php/main-category.html/option+2');

        $response = $this->getResponse()->getBody();

        $urlContainPath = strpos($response, 'http://localhost/index.php/main-category.html/option') !== false;

        $this->assertTrue($urlContainPath);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
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
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
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

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
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
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 0
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
     */
    public function testItReturnsCorrectUrlFilterUrlIfMaskingIsEnabledButShortFilterUrlIsDisabled()
    {
        $this->filterHelper->method('isFilterMasked')->willReturn(true);

        $this->dispatch('http://localhost/index.php/?multiselect_attribute=Option');

        $response = $this->getResponse()->getBody();

        $urlContainPath = strpos($response, 'http://localhost/index.php/?multiselect_attribute=Option') !== false;

        $this->assertTrue($urlContainPath);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products_with_brand.php
     */
    public function testItReturnsCorrectFilteredProductsOnBrandPage()
    {
        if (!$this->moduleManager->isEnabled('MageSuite_BrandManagement')) {
            $this->markTestSkipped('Test skipped because MageSuite_BrandManagement module is disabled');
        }

        $this->filterHelper->method('isFilterMasked')->willReturn(true);

        $this->dispatch('http://localhost/index.php/brands/test_brand/option+2');

        $response = $this->getResponse()->getBody();

        $this->assertTrue(
            strpos($response, 'Product with option 1 and 2') !== false
        );

        $this->assertTrue(
            strpos($response, 'Product with option 2 only') !== false
        );

        $this->assertFalse(
            strpos($response, 'Product with option 1 only') !== false
        );
    }
}
