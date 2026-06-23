<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($action = $request->get('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($docNo = $request->get('document_no')) {
            $query->where('document_no', 'like', "%{$docNo}%");
        }

        return view('audit-logs.index', [
            'records' => $query->paginate(25)->withQueryString(),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'action' => $action,
            'userId' => $userId,
            'from' => $from ?? now()->subDays(7)->toDateString(),
            'to' => $to ?? now()->toDateString(),
            'documentNo' => $docNo,
        ]);
    }
}
