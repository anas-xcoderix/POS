<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\DiscountRule;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private SettingService $settings) {}

    public function index(): View
    {
        return view('settings.index', [
            'settings' => $this->settings->all(),
            'discountRules' => DiscountRule::with(['customer', 'brand'])->orderByDesc('priority')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = $request->input('form_section', 'general');

        if ($section === 'gl') {
            $data = $request->validate([
                'auto_post_gl' => 'boolean',
                'gl_cash' => 'nullable|string|max:30',
                'gl_accounts_receivable' => 'nullable|string|max:30',
                'gl_inventory' => 'nullable|string|max:30',
                'gl_vat_input' => 'nullable|string|max:30',
                'gl_accounts_payable' => 'nullable|string|max:30',
                'gl_vat_payable' => 'nullable|string|max:30',
                'gl_sales_revenue' => 'nullable|string|max:30',
                'gl_cogs' => 'nullable|string|max:30',
            ]);
            $data['auto_post_gl'] = $request->boolean('auto_post_gl') ? '1' : '0';
        } else {
            $data = $request->validate([
                'default_vat_rate' => 'required|numeric|min:0|max:100',
                'price_level_retail' => 'required|in:1,2,3',
                'price_level_wholesale' => 'required|in:1,2,3',
                'price_level_corporate' => 'required|in:1,2,3',
                'enforce_credit_limit' => 'boolean',
                'company_name' => 'required|string|max:255',
            ]);
            $data['enforce_credit_limit'] = $request->boolean('enforce_credit_limit') ? '1' : '0';
        }

        $this->settings->setMany($data, 'general');

        return back()->with('success', 'Settings saved.');
    }

    public function storeDiscountRule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rule_type' => 'required|in:customer,brand,customer_type',
            'customer_id' => 'nullable|exists:customers,id',
            'brand_id' => 'nullable|exists:brands,id',
            'customer_type' => 'nullable|string|max:30',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'price_level' => 'nullable|in:1,2,3',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        DiscountRule::create($data);

        return back()->with('success', 'Discount rule added.');
    }

    public function destroyDiscountRule(DiscountRule $discountRule): RedirectResponse
    {
        $discountRule->delete();

        return back()->with('success', 'Discount rule removed.');
    }
}
