<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Part;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function __construct(private SalesService $salesService) {}

    public function resolve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'customer_id' => 'nullable|exists:customers,id',
            'quantity' => 'nullable|numeric|min:0.01',
        ]);

        $line = $this->salesService->resolveLinePricing(
            (int) $data['part_id'],
            $data['customer_id'] ?? null,
            auth()->id()
        );

        $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $availableCredit = $customer ? app(\App\Services\CreditService::class)->availableCredit($customer) : null;

        return response()->json([
            ...$line,
            'quantity' => (float) ($data['quantity'] ?? 1),
            'customer_balance' => $customer ? (float) $customer->balance : null,
            'customer_credit_limit' => $customer ? (float) $customer->credit_limit : null,
            'available_credit' => $availableCredit,
        ]);
    }
}
