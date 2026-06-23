<?php

namespace App\Services;

use App\Models\Location;
use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function __construct(
        private SalesService $salesService,
        private AuditService $audit,
        private BranchScopeService $branchScope,
    ) {}

    public function openSession(PosTerminal $terminal, float $openingFloat, int $userId): PosSession
    {
        $existing = PosSession::where('pos_terminal_id', $terminal->id)->where('status', 'open')->first();
        if ($existing) {
            throw new \RuntimeException('Terminal already has an open session.');
        }

        $session = PosSession::create([
            'session_no' => 'POS-'.now()->format('YmdHis'),
            'pos_terminal_id' => $terminal->id,
            'branch_id' => $terminal->branch_id,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_float' => $openingFloat,
            'status' => 'open',
        ]);

        $this->audit->log('pos.session_opened', $session, null, $session->toArray(), $session->session_no);

        return $session;
    }

    public function closeSession(PosSession $session, float $closingFloat): PosSession
    {
        $session->update([
            'closing_float' => $closingFloat,
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        $this->audit->log('pos.session_closed', $session, null, ['closing_float' => $closingFloat], $session->session_no);

        return $session->fresh();
    }

    public function quickSale(PosSession $session, array $data, array $items, bool $post = true): SalesInvoice
    {
        $this->branchScope->assertBranchAccess($session->branch_id);

        $terminal = $session->posTerminal;
        $defaultLocation = $terminal->default_location_id
            ?? Location::where('branch_id', $session->branch_id)->where('location_type', 'showroom')->value('id')
            ?? Location::where('branch_id', $session->branch_id)->value('id');

        foreach ($items as &$item) {
            $item['location_id'] = $item['location_id'] ?? $defaultLocation;
        }

        $data = array_merge($data, [
            'branch_id' => $session->branch_id,
            'pos_session_id' => $session->id,
            'source' => 'pos',
            'invoice_type' => 'cash',
            'status' => $post ? 'posted' : 'draft',
            'created_by' => $session->user_id,
        ]);

        $invoice = $this->salesService->createInvoice($data, $items, $post);
        $session->increment('total_sales', (float) $invoice->total_amount);
        $this->audit->log('pos.sale', $invoice, null, ['total' => $invoice->total_amount], $invoice->invoice_no);

        return $invoice;
    }
}
