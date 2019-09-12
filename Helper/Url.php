<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function encodeValue($value)
    {
        return urlencode($value);
    }

    public function decodeValue($value)
    {
        return urldecode($value);
    }
}
