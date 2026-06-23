<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChequeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cheque::with(['customer', 'vendor', 'branch'])->latest('cheque_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('cheques.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('cheques.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'vendors' => Vendor::where('is_active', true)->get(),
            'bankAccounts' => Account::where('is_active', true)->where('account_type', 'asset')->orderBy('account_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cheque_no' => 'required|string|max:50',
            'cheque_type' => 'required|in:received,issued',
            'customer_id' => 'nullable|exists:customers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'bank_account_id' => 'nullable|exists:accounts,id',
            'branch_id' => 'required|exists:branches,id',
            'cheque_date' => 'required|date',
            'due_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|string',
            'bank_name' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        Cheque::create($data);

        return redirect()->route('cheques.index')->with('success', __('messages.cheque.recorded'));
    }

    public function update(Request $request, Cheque $cheque): RedirectResponse
    {
        $cheque->update($request->validate([
            'status' => 'required|in:pending,cleared,bounced,cancelled',
        ]));

        return back()->with('success', __('messages.cheque.status_updated'));
    }
}
