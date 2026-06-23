<?php

namespace App\Services;

use App\Models\FiscalPeriod;
use Carbon\Carbon;

class FiscalPeriodService
{
    public function assertOpen(string|\DateTimeInterface $date): void
    {
        $carbon = Carbon::parse($date);
        $period = FiscalPeriod::firstOrCreate(
            ['year' => $carbon->year, 'month' => $carbon->month],
            ['is_closed' => false]
        );

        if ($period->is_closed) {
            throw new \RuntimeException('Fiscal period '.$period->label().' is closed. Cannot post transactions.');
        }
    }

    public function close(int $year, int $month, ?int $userId = null): FiscalPeriod
    {
        $period = FiscalPeriod::firstOrCreate(
            ['year' => $year, 'month' => $month],
            ['is_closed' => false]
        );

        $period->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);

        return $period->fresh();
    }

    public function reopen(int $year, int $month): FiscalPeriod
    {
        $period = FiscalPeriod::where('year', $year)->where('month', $month)->firstOrFail();
        $period->update([
            'is_closed' => false,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return $period->fresh();
    }
}
