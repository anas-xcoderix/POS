<?php

namespace App\Services;

use App\Models\Part;

class TaxService
{
    public function __construct(private SettingService $settings) {}

    public function resolveVatPercent(?Part $part = null): float
    {
        if ($part?->vat_code) {
            $mapped = $this->settings->get("vat_rate_{$part->vat_code}");
            if ($mapped !== null) {
                return (float) $mapped;
            }
        }

        return $this->settings->getFloat('default_vat_rate', 15);
    }

    public function calculateLine(float $quantity, float $unitPrice, float $discountPercent = 0, ?float $vatPercent = null, ?Part $part = null): array
    {
        $vatPercent ??= $this->resolveVatPercent($part);
        $lineSubtotal = round($quantity * $unitPrice, 4);
        $discountAmount = round($lineSubtotal * ($discountPercent / 100), 4);
        $net = round($lineSubtotal - $discountAmount, 4);
        $vatAmount = round($net * ($vatPercent / 100), 4);
        $lineTotal = round($net + $vatAmount, 2);

        return [
            'line_subtotal' => $lineSubtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'net' => $net,
            'vat_percent' => $vatPercent,
            'vat_amount' => $vatAmount,
            'line_total' => $lineTotal,
        ];
    }

    public function summarizeDocument(array $lines, float $headerDiscountAmount = 0): array
    {
        $subtotal = round(collect($lines)->sum('line_subtotal'), 2);
        $lineDiscounts = round(collect($lines)->sum('discount_amount'), 2);
        $vatAmount = round(collect($lines)->sum('vat_amount'), 2);
        $total = round(collect($lines)->sum('line_total') - $headerDiscountAmount, 2);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $lineDiscounts + $headerDiscountAmount,
            'vat_amount' => $vatAmount,
            'total_amount' => max(0, $total),
        ];
    }
}
