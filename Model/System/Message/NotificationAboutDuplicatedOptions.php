<?php

namespace MageSuite\SeoLinkMasking\Model\System\Message;

class NotificationAboutDuplicatedOptions implements \Magento\Framework\Notification\MessageInterface
{
    protected \MageSuite\SeoLinkMasking\Helper\Configuration $configuration;

    protected \MageSuite\SeoLinkMasking\Service\DuplicatedOptionsNotifier $duplicatedOptionsNotifier;

    protected \MageSuite\SeoLinkMasking\Helper\Url $url;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \MageSuite\SeoLinkMasking\Service\DuplicatedOptionsNotifier $duplicatedOptionsNotifier,
        \MageSuite\SeoLinkMasking\Helper\Url $url
    ) {
        $this->configuration = $configuration;
        $this->duplicatedOptionsNotifier = $duplicatedOptionsNotifier;
        $this->url = $url;
    }

    public function getIdentity(): string
    {
        return hash('sha256', 'NOTIFICATION_ABOUT_DUPLICATED_OPTIONS_IN_ATTRIBUTES_IN_CASE_WHEN_FILTER_NAMES_IN_URL_IS_ENABLED');
    }

    public function isDisplayed(): bool
    {
        if (!$this->configuration->isLinkMaskingEnabled()) {
            return false;
        }

        if (!$this->configuration->isShortFilterUrlEnabled()) {
            return false;
        }

        if (!$this->configuration->isDisplayingWarningAboutDuplicatedOptionsEnabled()) {
            return false;
        }

        return $this->duplicatedOptionsNotifier->isWarningDisplayed();
    }

    public function getText(): string
    {
        $message = __('<b>Module MageSuite SEO Link Masking: Warning</b> - Please remove all option duplicates in attributes listed in below pairs &#34;<b>option</b>: attributes list separated by commas&#34;. They have to be unique.');
        $message .= '<br /><br />';
        $duplicatedOptions = $this->duplicatedOptionsNotifier->getDuplicatedOptionsInAttributes();

        foreach ($duplicatedOptions as $option => $attributes) {
            $message .= sprintf('%s: ', sprintf('<b>%s</b>', $this->url->decodeValue($option)));
            $message .= implode(', ', $attributes) . '<br />';
        }

        $message .= '<br />';
        $message .= __('Please keep in mind, that options are case insensitive in URLs, so <b>Option</b> is equal to <b>option</b>.'  . '<br />');
        $message .= __('If you don&#39;t want, or can not remove these options, you can also disable <b>&#34;Enable hiding of filter names in URL&#34;</b> option on configuration page of module.' . '<br />');
        $message .= __('Please be aware, that when above options are not unique - affected <b>products will be unreachable in shop for customers</b> when <b>&#34;Enable hiding of filter names in URL&#34;</b> option is enabled on configuration page of module.');

        return $message;
    }

    public function getSeverity(): string
    {
        return self::SEVERITY_MAJOR;
    }
}
