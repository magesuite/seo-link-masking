<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_SPACE_REPLACEMENT_CHAR = '+';

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MageSuite\SeoLinkMasking\Helper\Configuration $configuration
    ) {
        parent::__construct($context);

        $this->configuration = $configuration;
    }

    public function encodeValue($value)
    {
        $value = strtolower($value);
        $value = $this->removeExcludedCharacters($value);
        $value = urlencode($value);

        $spaceReplacementCharacter = $this->configuration->getSpaceReplacementCharacter();

        if (!$spaceReplacementCharacter || $spaceReplacementCharacter == self::DEFAULT_SPACE_REPLACEMENT_CHAR) {
            return $value;
        }

        return str_replace(self::DEFAULT_SPACE_REPLACEMENT_CHAR, $spaceReplacementCharacter, $value);
    }

    public function decodeValue($value)
    {
        $spaceReplacementCharacter = $this->configuration->getSpaceReplacementCharacter();

        if ($spaceReplacementCharacter && $spaceReplacementCharacter != self::DEFAULT_SPACE_REPLACEMENT_CHAR) {
            $value = str_replace($spaceReplacementCharacter, self::DEFAULT_SPACE_REPLACEMENT_CHAR, $value);
        }

        return urldecode($value);
    }

    protected function removeExcludedCharacters($value)
    {
        $excludedCharacters = $this->configuration->getExcludedCharacters();

        if (empty($excludedCharacters)) {
            return $value;
        }

        $result = str_replace($excludedCharacters, '', $value);
        return preg_replace('!\s+!', ' ', $result);
    }
}
