<?php

namespace MageSuite\SeoLinkMasking\Plugin\Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Attribute;

class AddLinkMaskingToFilterData
{
    const LINK_MASKING_ENDPOINT = 'linkmasking/filter/redirect';

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postHelper;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Data\Helper\PostHelper $postHelper
    ) {
        $this->configuration = $configuration;
        $this->url = $url;
        $this->postHelper = $postHelper;
    }

    public function afterGetJsLayout(\Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Attribute $subject, $result)
    {
        if (empty($result)) {
            return $result;
        }

        $jsLayoutConfig = json_decode($result, true);

        if (!$this->configuration->isLinkMaskingEnabled()) {
            $jsLayoutConfig['isLinkMaskingEnabled'] = false;
            return json_encode($jsLayoutConfig);
        }

        $jsLayoutConfig['isLinkMaskingEnabled'] = $subject->getFilter()->getIsLinkMaskingEnabled();

        if (!$jsLayoutConfig['isLinkMaskingEnabled'] || empty($jsLayoutConfig['items'])) {
            return json_encode($jsLayoutConfig);
        }

        $linkmaskingUrl = $this->url->getUrl(self::LINK_MASKING_ENDPOINT);

        foreach ($jsLayoutConfig['items'] as &$item) {
            $item['url'] = $this->postHelper->getPostData($linkmaskingUrl, ['url' => $item['url']]);
        }

        return json_encode($jsLayoutConfig);
    }
}
