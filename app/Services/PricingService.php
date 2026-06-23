<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DiscountRule;
use App\Models\Part;

class PricingService
{
    public function __construct(private SettingService $settings) {}

    public function resolveUnitPrice(Part $part, ?Customer $customer = null, ?int $userId = null): array
    {
        $priceLevel = $this->resolvePriceLevel($part, $customer);
        $basePrice = $this->priceForLevel($part, $priceLevel);
        $discountPercent = $this->resolveDiscountPercent($part, $customer);
        $discountPercent = $this->capDiscountForUser($discountPercent, $userId);

        return [
            'price_level' => $priceLevel,
            'base_price' => $basePrice,
            'discount_percent' => $discountPercent,
            'unit_price' => $basePrice,
        ];
    }

    public function resolvePriceLevel(Part $part, ?Customer $customer): int
    {
        if ($customer) {
            $ruleLevel = $this->matchingRules($part, $customer)
                ->whereNotNull('price_level')
                ->sortByDesc('priority')
                ->first()?->price_level;

            if ($ruleLevel) {
                return (int) $ruleLevel;
            }

            if ($customer->price_level) {
                return (int) $customer->price_level;
            }

            $typeLevel = match (strtolower((string) $customer->customer_type)) {
                'wholesale' => (int) $this->settings->get('price_level_wholesale', '2'),
                'corporate' => (int) $this->settings->get('price_level_corporate', '3'),
                default => (int) $this->settings->get('price_level_retail', '1'),
            };

            return $typeLevel;
        }

        return 1;
    }

    public function resolveDiscountPercent(Part $part, ?Customer $customer): float
    {
        $discount = (float) ($customer?->discount_percent ?? 0);

        $ruleDiscount = (float) $this->matchingRules($part, $customer)
            ->sortByDesc('priority')
            ->max('discount_percent');

        return max($discount, $ruleDiscount);
    }

    public function capDiscountForUser(float $discountPercent, ?int $userId = null): float
    {
        $user = $userId ? \App\Models\User::find($userId) : auth()->user();
        if (! $user) {
            return $discountPercent;
        }

        if ($user->role === 'admin') {
            return $discountPercent;
        }

        return min($discountPercent, (float) $user->max_discount_percent);
    }

    protected function priceForLevel(Part $part, int $level): float
    {
        return match ($level) {
            2 => (float) ($part->price_2 > 0 ? $part->price_2 : $part->list_price),
            3 => (float) ($part->price_3 > 0 ? $part->price_3 : ($part->price_2 > 0 ? $part->price_2 : $part->list_price)),
            default => (float) $part->list_price,
        };
    }

    protected function matchingRules(Part $part, ?Customer $customer)
    {
        return DiscountRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($part, $customer) {
                $q->where(function ($q) use ($customer) {
                    $q->where('rule_type', 'customer')
                        ->when($customer, fn ($q) => $q->where('customer_id', $customer->id), fn ($q) => $q->whereRaw('1=0'));
                })->orWhere(function ($q) use ($part) {
                    $q->where('rule_type', 'brand')->where('brand_id', $part->brand_id);
                })->orWhere(function ($q) use ($customer) {
                    $q->where('rule_type', 'customer_type')
                        ->when($customer?->customer_type, fn ($q) => $q->where('customer_type', $customer->customer_type), fn ($q) => $q->whereRaw('1=0'));
                });
            })
            ->get();
    }
}
