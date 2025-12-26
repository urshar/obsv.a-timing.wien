<?php

namespace App\Support\Concerns;

use App\Services\Lenex\LenexXml;
use SimpleXMLElement;

trait LenexXmlValueHelpers
{
    private function strAttrNullable(SimpleXMLElement $node, string $attr): ?string
    {
        $v = LenexXml::attr($node, $attr);
        if ($v === null) {
            return null;
        }

        $v = trim($v);

        return $v === '' ? null : $v;
    }

    private function intAttrNullable(SimpleXMLElement $node, string $attr): ?int
    {
        $v = LenexXml::attr($node, $attr);
        if ($v === null) {
            return null;
        }

        $v = trim($v);
        if ($v === '' || ! is_numeric($v)) {
            return null;
        }

        return (int) $v;
    }

    private function genderChar(?string $genderRaw): ?string
    {
        if (! $genderRaw) {
            return null;
        }

        $g = strtoupper(trim($genderRaw));

        return $g === '' ? null : substr($g, 0, 1);
    }

    private function boolAttr(SimpleXMLElement $node, array $attrs): ?bool
    {
        foreach ($attrs as $attr) {
            $raw = LenexXml::attr($node, $attr);
            if ($raw === null) {
                continue;
            }

            $raw = strtolower(trim($raw));
            if ($raw === '') {
                continue;
            }

            return in_array($raw, ['1', 'true', 'yes', 'y'], true);
        }

        return null;
    }
}
