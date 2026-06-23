<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\DB;

class CurrencyService
{
    public function baseCurrency(): ?Currency
    {
        return Currency::where('is_base', true)->first()
            ?? Currency::where('code', 'SAR')->first();
    }

    public function rateFor(Currency $currency, ?\DateTimeInterface $date = null): float
    {
        if ($currency->is_base) {
            return 1.0;
        }

        $date = $date ?? now();

        $historical = CurrencyRate::where('currency_id', $currency->id)
            ->where('rate_date', '<=', $date)
            ->orderByDesc('rate_date')
            ->value('exchange_rate');

        return (float) ($historical ?? $currency->exchange_rate ?? 1);
    }

    public function convertToBase(float $amount, Currency $currency, ?\DateTimeInterface $date = null): float
    {
        return round($amount * $this->rateFor($currency, $date), 2);
    }

    public function convertFromBase(float $baseAmount, Currency $currency, ?\DateTimeInterface $date = null): float
    {
        $rate = $this->rateFor($currency, $date);

        return $rate > 0 ? round($baseAmount / $rate, 2) : $baseAmount;
    }

    public function applyToDocument(array &$data): array
    {
        if (empty($data['currency_id'])) {
            $base = $this->baseCurrency();
            $data['currency_id'] = $base?->id;
            $data['exchange_rate'] = 1;
        } else {
            $currency = Currency::findOrFail($data['currency_id']);
            $data['exchange_rate'] = $data['exchange_rate'] ?? $this->rateFor($currency);
        }

        if (isset($data['total_amount'])) {
            $data['foreign_total'] = round((float) $data['total_amount'], 2);
            if (($data['exchange_rate'] ?? 1) != 1) {
                $data['total_amount'] = $this->convertToBase((float) $data['foreign_total'], Currency::find($data['currency_id']));
            }
        }

        return $data;
    }

    public function setRate(Currency $currency, float $rate, ?int $userId = null): CurrencyRate
    {
        return DB::transaction(function () use ($currency, $rate, $userId) {
            $currency->update(['exchange_rate' => $rate]);

            return CurrencyRate::updateOrCreate(
                ['currency_id' => $currency->id, 'rate_date' => now()->toDateString()],
                ['exchange_rate' => $rate, 'created_by' => $userId]
            );
        });
    }
}
