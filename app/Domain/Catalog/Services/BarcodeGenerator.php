<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Support\Str;

class BarcodeGenerator
{
    public function generate(string $prefix = 'CS'): string
    {
        do {
            $ulid = (string) Str::ulid();
            $digits = preg_replace('/[^0-9A-Z]/', '', strtoupper($ulid));
            $barcode = strtoupper($prefix).substr($digits, -10);
        } while (ProductVariant::query()->where('barcode', $barcode)->exists());

        return $barcode;
    }
}
