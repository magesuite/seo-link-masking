<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Controller;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RouterTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected const DEFAULT_OPTION_SEPARATOR = '--';
    protected const DEFAULT_STORE_ID = 1;

    protected ?\Magento\Framework\App\CacheInterface $cache;

    public function setUp(): void
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->cache = $objectManager->get(\Magento\Framework\App\CacheInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     */
    public function testItSetFiltersByValues(): void
    {
        $cacheKey = $this->getFilterableAttributeOptionsCacheKey(self::DEFAULT_STORE_ID);
        $this->cache->remove($cacheKey);

        $this->dispatch('/test-category/option+1');

        $assertContains = $this->getAssertContainsMethod();
        $this->$assertContains('Multiselect Attribute', $this->getResponse()->getBody());

        $parameters = $this->getRequest()->getParams();

        $this->assertArrayHasKey('multiselect_attribute', $parameters);
        $this->assertEquals('Option 1', $parameters['multiselect_attribute']);

        $this->cache->remove($cacheKey);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture default/seo/link_masking/space_replacement_character _
     */
    public function testItSetFiltersIfSpaceReplacementCharIsChanged(): void
    {
        $cacheKey = $this->getFilterableAttributeOptionsCacheKey(self::DEFAULT_STORE_ID);
        $this->cache->remove($cacheKey);

        $this->dispatch('/test-category/option_1');

        $assertContains = $this->getAssertContainsMethod();
        $this->$assertContains('Multiselect Attribute', $this->getResponse()->getBody());

        $parameters = $this->getRequest()->getParams();

        $this->assertArrayHasKey('multiselect_attribute', $parameters);
        $this->assertEquals('Option 1', $parameters['multiselect_attribute']);

        $this->cache->remove($cacheKey);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture default/seo/link_masking/excluded_characters &
     */
    public function testItSetFiltersIfExcludedCharactersAreSet(): void
    {
        $cacheKey = $this->getFilterableAttributeOptionsCacheKey(self::DEFAULT_STORE_ID);
        $this->cache->remove($cacheKey);

        $this->dispatch('/test-category/option+with+special+char');

        $assertContains = $this->getAssertContainsMethod();
        $this->$assertContains('Multiselect Attribute', $this->getResponse()->getBody());

        $parameters = $this->getRequest()->getParams();

        $this->assertArrayHasKey('multiselect_attribute', $parameters);
        $this->assertEquals('Option with & special char', $parameters['multiselect_attribute']);

        $this->cache->remove($cacheKey);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     */
    public function testItSetFilterWithMultipleOptions(): void
    {
        $cacheKey = $this->getFilterableAttributeOptionsCacheKey(self::DEFAULT_STORE_ID);
        $this->cache->remove($cacheKey);

        $this->dispatch('/test-category/option+1--option+2');

        $assertContains = $this->getAssertContainsMethod();
        $this->$assertContains('Multiselect Attribute', $this->getResponse()->getBody());

        $parameters = $this->getRequest()->getParams();

        $this->assertArrayHasKey('multiselect_attribute', $parameters);
        $this->assertCount(2, $parameters['multiselect_attribute']);
        $this->assertEquals(['Option 1', 'Option 2'], $parameters['multiselect_attribute']);

        $this->cache->remove($cacheKey);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     */
    public function testItSetFilterWithMultipleOptionsWithSlash(): void
    {
        $cacheKey = $this->getFilterableAttributeOptionsCacheKey(self::DEFAULT_STORE_ID);
        $this->cache->remove($cacheKey);

        $this->dispatch('/test-category/option+1/option+2');

        $assertContains = $this->getAssertContainsMethod();
        $this->$assertContains('Multiselect Attribute', $this->getResponse()->getBody());

        $parameters = $this->getRequest()->getParams();

        $this->assertArrayHasKey('multiselect_attribute', $parameters);
        $this->assertCount(2, $parameters['multiselect_attribute']);
        $this->assertEquals(['Option 1', 'Option 2'], $parameters['multiselect_attribute']);

        $this->cache->remove($cacheKey);
    }

    public static function loadFilterableProducts(): void
    {
        require __DIR__ . '/../_files/filterable_products.php';
    }

    public static function loadFilterableProductsRollback(): void
    {
        require __DIR__ . '/../_files/filterable_products_rollback.php';
    }

    protected function getFilterableAttributeOptionsCacheKey(?int $storeId = null): string
    {
        return sprintf(\MageSuite\SeoLinkMasking\Service\FilterableAttributeOptionsProvider::CACHE_TAG, $storeId);
    }

    protected function getAssertContainsMethod(): string
    {
        return method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';
    }
}
