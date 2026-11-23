<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\{CompanyActivity, Invoice};
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $r)
    {
        $q = Invoice::latest('id');

        if ($s = $r->query('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('invoice_no', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%");
            });
        }

        if ($r->filled('status')) {
            $q->where('status', $r->query('status'));
        }

        return view('superadminconsole.pages.console.invoices.index', [
            'invoices' => $q->paginate(10)->withQueryString(),
            'stats' => [
                'billed' => (int) Invoice::sum('total'),
                'paid' => (int) Invoice::where('status', 'paid')->sum('total'),
                'pending' => (int) Invoice::where('status', 'pending')->sum('total'),
                'month' => Invoice::where(
                    'created_at',
                    '>=',
                    now()->startOfMonth()
                )->count(),
            ],
        ]);
    }

    public function show(Invoice $invoice)
    {
        return view(
            'superadminconsole.pages.console.invoices.show',
            ['inv' => $invoice]
        );
    }

    public function markPaid(Request $r, Invoice $invoice)
    {
        $d = $r->validate([
            'payment_mode' => 'required|in:' . implode(',', Invoice::MODES),
        ]);

        $invoice->update([
            'status' => 'paid',
            'payment_mode' => $d['payment_mode'],
            'paid_at' => now(),
        ]);

        if ($invoice->company) {
            CompanyActivity::log(
                $invoice->company,
                'payment',
                "Invoice {$invoice->invoice_no} marked paid ({$d['payment_mode']})"
            );
        }

        return response()->json([
            'message' => 'Invoice marked as paid.',
        ]);
    }
}