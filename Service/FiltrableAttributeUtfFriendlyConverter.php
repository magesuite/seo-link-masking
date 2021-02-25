<?php

namespace MageSuite\SeoLinkMasking\Service;

class FiltrableAttributeUtfFriendlyConverter
{
    public function convertOptions($options)
    {
        foreach ($options as $optionKey => $option) {
            $options[$this->convertFilterParam($optionKey)] = $option;
        }

        return $options;
    }

    public function convertFilterParams($filteredValues)
    {
        foreach ($filteredValues as &$filteredValue) {
            $filteredValue = $this->convertFilterParam($filteredValue);
        }

        return $filteredValues;
    }


    protected function convertFilterParam($filteredValue)
    {
        $filteredValue = urldecode($filteredValue);

        $convertTable = [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ß' => 'ss',
            'ẞ' => 'Ss'
        ];

        $utfFriedlyParameter = str_replace(array_keys($convertTable),
            array_values($convertTable),
            $filteredValue);

        return $utfFriedlyParameter;
    }
}