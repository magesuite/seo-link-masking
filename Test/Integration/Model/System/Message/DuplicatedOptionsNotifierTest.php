<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Model\System\Message;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DuplicatedOptionsNotifierTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\SeoLinkMasking\Service\DuplicatedOptionsNotifier $duplicatedOptionsNotifier;

    public const RESTRICT_TESTED_ATTRIBUTES_TO_GIVEN_LIST = [
        'select_attribute_with_not_unique_options',
        'select_attribute_with_unique_options',
        'first_attribute_with_unique_options_per_attribute',
        'second_attribute_with_unique_options_per_attribute'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->duplicatedOptionsNotifier = $this->objectManager->get(\MageSuite\SeoLinkMasking\Service\DuplicatedOptionsNotifier::class);
        $this->duplicatedOptionsNotifier->restrictAttributesCheckToGivenList(self::RESTRICT_TESTED_ATTRIBUTES_TO_GIVEN_LIST);
    }

    /**
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/select_attribute_with_unique_options_per_attribute.php
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_displaying_warning_about_duplicated_options_enabled 1
     */
    public function testItWillNotDisplayWarningIfAllAttributesOptionsAreUnique()
    {
        $this->assertFalse($this->duplicatedOptionsNotifier->isWarningDisplayed());
    }

    /**
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/select_attribute_with_not_unique_options_per_attribute.php
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_displaying_warning_about_duplicated_options_enabled 1
     */
    public function testItWillDisplayWarningIfNotAllAttributesOptionsAreUnique()
    {
        $this->assertTrue($this->duplicatedOptionsNotifier->isWarningDisplayed());
        $this->assertEquals(
            $this->provideArrayWithKeyAsDuplicatedOptionAndValueAsAttributeWhereItAppears(),
            $this->duplicatedOptionsNotifier->getDuplicatedOptionsInAttributes()
        );
    }

    /**
     * @magentoDataFixture MageSuite_SeoLinkMasking::Test/Integration/_files/select_attribute_with_unique_options_per_attribute_but_repeatable_in_another_attributes.php
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_displaying_warning_about_duplicated_options_enabled 1
     */
    public function testItWillDisplayWarningIfAttributesOptionsAreUniquePerAttributeButRepeatableInAnotherAttributes()
    {
        $this->assertTrue($this->duplicatedOptionsNotifier->isWarningDisplayed());
        $this->assertEquals(
            $this->provideArrayWithKeysAsDuplicatedOptionsAndValuesAsAttributesWhereOptionsAppears(),
            $this->duplicatedOptionsNotifier->getDuplicatedOptionsInAttributes()
        );
    }

    /**
     * @before
     * @after
     */
    public function cleanAttributesWithOptionsCache(): void
    {
        $attributesWithOptionsCache = \Magento\TestFramework\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\CacheInterface::class);
        $attributesWithOptionsCache->clean();
    }

    public function provideArrayWithKeyAsDuplicatedOptionAndValueAsAttributeWhereItAppears(): array
    {
        return [
            'not_unique_option' => [
                '0' => 'select_attribute_with_not_unique_options'
            ]
        ];
    }

    public function provideArrayWithKeysAsDuplicatedOptionsAndValuesAsAttributesWhereOptionsAppears(): array
    {
        return [
            'unique_option_1' => [
                '0' => 'first_attribute_with_unique_options_per_attribute',
                '1' => 'second_attribute_with_unique_options_per_attribute'
            ],
            'unique_option_2' => [
                '0' => 'first_attribute_with_unique_options_per_attribute',
                '1' => 'second_attribute_with_unique_options_per_attribute'
            ],
            'unique_option_3' => [
                '0' => 'first_attribute_with_unique_options_per_attribute',
                '1' => 'second_attribute_with_unique_options_per_attribute'
            ]
        ];
    }
}
