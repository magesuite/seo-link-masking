<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Test\Integration\Model\System\Message;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class NotificationAboutDuplicatedOptionsTest extends \PHPUnit\Framework\TestCase
{
    protected const XSS_PAYLOAD = '<img src=x onerror=alert(document.domain)>';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\SeoLinkMasking\Model\System\Message\NotificationAboutDuplicatedOptions $notification;

    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->notification = $this->objectManager->get(
            \MageSuite\SeoLinkMasking\Model\System\Message\NotificationAboutDuplicatedOptions::class
        );

        $this->objectManager
            ->get(\MageSuite\SeoLinkMasking\Service\DuplicatedOptionsNotifier::class)
            ->restrictAttributesCheckToGivenList(['select_attribute_with_xss_payload_duplicate_options']);
    }

    #[\PHPUnit\Framework\Attributes\Before]
    #[\PHPUnit\Framework\Attributes\After]
    public function cleanAttributesWithOptionsCache(): void
    {
        \Magento\TestFramework\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\CacheInterface::class)
            ->clean();
    }

    #[\Magento\TestFramework\Fixture\DataFixture(
        'MageSuite_SeoLinkMasking::Test/Integration/_files/select_attribute_with_xss_payload_duplicate_options.php'
    )]
    public function testItEscapesDuplicatedOptionLabelsInTheNotificationText(): void
    {
        $text = $this->notification->getText();

        $this->assertStringNotContainsString(self::XSS_PAYLOAD, $text);
        $this->assertStringContainsString(
            sprintf(
                '<b>%s</b>: select_attribute_with_xss_payload_duplicate_options<br />',
                htmlspecialchars(self::XSS_PAYLOAD, ENT_QUOTES | ENT_SUBSTITUTE)
            ),
            $text
        );
        $this->assertStringContainsString('<b>Module MageSuite SEO Link Masking: Warning</b>', $text);
    }
}
