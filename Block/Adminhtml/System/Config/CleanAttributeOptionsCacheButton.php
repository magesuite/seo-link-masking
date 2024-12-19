<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Block\Adminhtml\System\Config;

class CleanAttributeOptionsCacheButton extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setData('value', __('Clean Attribute Options Cache'));
        $element->setData('class', 'action-default');
        $element->setData('onclick', "window.location.assign('{$this->getActionUrl()}')");

        return parent::_getElementHtml($element);
    }

    public function getActionUrl(): string
    {
        return $this->_urlBuilder->getUrl('linkmasking/attribute/cleanCache');
    }

    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        return '';
    }
}
