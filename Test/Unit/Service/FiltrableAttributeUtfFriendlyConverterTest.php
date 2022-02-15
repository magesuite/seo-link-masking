<?php

namespace MageSuite\SeoLinkMasking\Test\Unit\Service;

class FiltrableAttributeUtfFriendlyConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurationStub;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FiltrableAttributeUtfFriendlyConverter
     */
    protected $filtrableAttributeUtfFriendlyConverter;

    protected function setUp(): void
    {
        $this->configurationStub = $this->getMockBuilder(\MageSuite\SeoLinkMasking\Helper\Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filtrableAttributeUtfFriendlyConverter = new \MageSuite\SeoLinkMasking\Service\FiltrableAttributeUtfFriendlyConverter($this->configurationStub);
    }

    /**
     * @dataProvider getFilteredValues
     * @param $filteredValues
     * @param $expectedFilteredValues
     * @param $excludedCharacters
     */
    public function testItReturnsCorrectValuesRoundUp($filteredValues, $expectedFilteredValues, $excludedCharacters)
    {
        $this->configurationStub->method('getExcludedCharacters')->willReturn($excludedCharacters);

        $convertedFilteredValues = $this->filtrableAttributeUtfFriendlyConverter->convertFilterParams([$filteredValues]);
        $this->assertEquals($expectedFilteredValues, $convertedFilteredValues[0]);
    }

    public static function getFilteredValues()
    {
        return [
            'regular param' => ['Parameter', 'Parameter', []],
            'param with german char' => ['Pärameter', 'Paerameter', []],
            'param with slash' => ['Para/meter', 'Para%2Fmeter', []],
            'param with slash and excluded slash' => ['Para/meter', 'Para/meter', ['/']],
            'param with special chars' => ['Para!=&^%meter', 'Para%21%3D%26%5E%25meter', []],
            'param with slash and german char' => ['Pära/meter', 'Paera%2Fmeter', []],
            'param with slash and german char and excluded slash' => ['Pära/meter', 'Paera/meter', ['/']]
        ];
    }
}
