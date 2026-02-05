<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class UpdateMetaRobotsTagTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected const ROBOTS_TAG_INDEX_FOLLOW = 'INDEX,FOLLOW';

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/only_one_filter_demasked 1
     */
    public function testItDoesntUpdateSeoMetaRobots(): void
    {
        $this->dispatch('/test-category/option+1');

        $assertContains = method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';

        $this->$assertContains(self::ROBOTS_TAG_INDEX_FOLLOW, $this->getResponse()->getBody());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/filterable_products.php
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/only_one_filter_demasked 1
     */
    public function testItUpdatesSeoMetaRobots(): void
    {
        $this->dispatch('/test-category/option+1--option+2');

        $assertContains = method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';

        $this->$assertContains('Multiselect Attribute', $this->getResponse()->getBody());
        $this->$assertContains(\MageSuite\SeoLinkMasking\Observer\UpdateMetaRobotsTag::ROBOTS_TAG_NOINDEX_FOLLOW, $this->getResponse()->getBody());
    }
}
