<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $currencyService) {}

    public function index(Request $request): View
    {
        $query = Currency::query()->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return view('currencies.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'symbol' => 'nullable|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->boolean('is_base')) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }

        Currency::create([
            ...$data,
            'is_base' => $request->boolean('is_base'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('messages.currency.created'));
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,'.$currency->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'symbol' => 'nullable|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->boolean('is_base')) {
            Currency::where('id', '!=', $currency->id)->update(['is_base' => false]);
        }

        $currency->update([
            ...$data,
            'is_base' => $request->boolean('is_base'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('messages.currency.updated'));
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        if ($currency->is_base) {
            return back()->with('error', __('messages.currency.cannot_delete_base'));
        }

        $currency->delete();

        return back()->with('success', __('messages.currency.deleted'));
    }

    public function setRate(Request $request, Currency $currency): RedirectResponse
    {
        $data = $request->validate([
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        try {
            $this->currencyService->setRate($currency, (float) $data['exchange_rate'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.currency.rate_updated'));
    }
}
