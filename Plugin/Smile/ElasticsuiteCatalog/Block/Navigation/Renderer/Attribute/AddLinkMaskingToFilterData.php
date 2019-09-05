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
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postHelper;

    public function __construct(
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration,
        \Magento\Framework\Data\Helper\PostHelper $postHelper
    ) {
        $this->configuration = $configuration;
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

        foreach ($jsLayoutConfig['items'] as &$item) {
            $item['url'] = $this->postHelper->getPostData(self::LINK_MASKING_ENDPOINT, ['url' => $item['url']]);
        }

        return json_encode($jsLayoutConfig);
    }
}
