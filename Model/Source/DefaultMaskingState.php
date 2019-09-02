<?php

namespace MageSuite\SeoLinkMasking\Model\Source;

class DefaultMaskingState extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Data\OptionSourceInterface
{
    const LINK_IS_MASKED = 1;
    const LINK_IS_DEMASKED = 0;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (empty($this->options)) {
            $this->options = [
                ['label' => __('Is Masked'), 'value' => self::LINK_IS_MASKED],
                ['label' => __('Is Demasked'), 'value' => self::LINK_IS_DEMASKED]
            ];
        }

        return $this->options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
