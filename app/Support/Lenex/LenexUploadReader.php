<?php

namespace App\Support\Lenex;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

class LenexUploadReader
{
    public static function toXmlString(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if (! $path) {
            throw new RuntimeException('Uploaded file has no readable path.');
        }

        // Read a small header to detect container format
        $fh = @fopen($path, 'rb');
        if (! $fh) {
            throw new RuntimeException('Uploaded file cannot be opened.');
        }
        $header = fread($fh, 4) ?: '';
        fclose($fh);

        // ZIP: "PK\x03\x04" / "PK\x05\x06" / "PK\x07\x08"
        if (str_starts_with($header, 'PK')) {
            return self::extractFromZip($path);
        }

        // GZIP: 1F 8B
        if (strlen($header) >= 2 && ord($header[0]) === 0x1F && ord($header[1]) === 0x8B) {
            $raw = file_get_contents($path);
            $xml = $raw !== false ? gzdecode($raw) : false;

            if ($xml === false || $xml === '') {
                throw new RuntimeException('GZIP could not be decoded.');
            }

            return $xml;
        }

        // Plain
        $xml = file_get_contents($path);
        if ($xml === false || $xml === '') {
            throw new RuntimeException('File could not be read.');
        }

        return $xml;
    }

    private static function extractFromZip(string $path): string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('ZIP could not be opened.');
        }

        // Prefer XML files inside the zip
        $candidateIndex = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (! is_string($name)) {
                continue;
            }

            $lower = strtolower($name);
            if (str_ends_with($lower, '.xml') || str_ends_with($lower, '.lef') || str_ends_with($lower, '.lxf')) {
                $candidateIndex = $i;
                break;
            }
        }

        // Fallback: first file
        if ($candidateIndex === null && $zip->numFiles > 0) {
            $candidateIndex = 0;
        }

        if ($candidateIndex === null) {
            $zip->close();
            throw new RuntimeException('ZIP contains no files.');
        }

        $contents = $zip->getFromIndex($candidateIndex);
        $zip->close();

        if (! is_string($contents) || $contents === '') {
            throw new RuntimeException('ZIP entry could not be read.');
        }

        return $contents;
    }
}
