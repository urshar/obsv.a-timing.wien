<?php

namespace App\Rules;

use App\Support\Lenex\LenexUploadReader;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Throwable;

class LenexFile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail(__('validation.lenex_file.invalid'));

            return;
        }

        $ext = strtolower($value->getClientOriginalExtension());
        if (! in_array($ext, ['lef', 'lxf', 'xml'], true)) {
            $fail(__('validation.lenex_file.extension'));

            return;
        }

        try {
            $xml = LenexUploadReader::toXmlString($value);
        } catch (Throwable) {
            $fail(__('validation.lenex_file.unreadable'));

            return;
        }

        // Robust: suche LENEX Root-Element (case-insensitive)
        if (stripos($xml, '<lenex') === false) {
            // Manche Dateien haben Namespace/Uppercase, stripos deckt das ab.
            $fail(__('validation.lenex_file.not_lenex_xml'));
        }
    }
}
