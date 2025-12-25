<?php

namespace App\Services\Lenex;

use RuntimeException;
use SimpleXMLElement;

class LenexXml
{
    public static function load(string $xmlString): SimpleXMLElement
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);
        if (! $xml) {
            $errors = libxml_get_errors();
            $msg = collect($errors)->map(fn ($e) => trim($e->message))->implode('; ');
            throw new RuntimeException('Invalid XML: '.$msg);
        }

        return $xml;
    }

    public static function attr(?SimpleXMLElement $node, string $name): ?string
    {
        if (! $node) {
            return null;
        }
        $attrs = $node->attributes();
        if (! $attrs) {
            return null;
        }

        return isset($attrs[$name]) ? (string) $attrs[$name] : null;
    }

    public static function text(?SimpleXMLElement $node): ?string
    {
        if (! $node) {
            return null;
        }
        $t = trim((string) $node);

        return $t === '' ? null : $t;
    }
}
