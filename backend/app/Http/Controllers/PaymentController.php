<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query();

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('payment_mode_id')) {
            $query->where('payment_mode_id', $request->payment_mode_id);
        }

        if ($request->filled('payment_mode_ref')) {
            $query->where('payment_mode_ref', 'like', '%' . $request->payment_mode_ref . '%');
        }

        // Filter: created_at date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        return $query->with("member", "mode")->paginate($request->per_page ?? 15);
    }

    public function paymentSummaryByMode(Request $request)
    {
        $query = Payment::query()->with('mode');

        // Optional date filtering
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $payments = $query->get();

        // Fetch all payment mode names from DB
        $expectedModes = \App\Models\PaymentMode::pluck('name')->mapWithKeys(fn($name) => [$name => formatCurrency(0)]);

        // Group and summarize payments by mode
        $summary = $payments->groupBy('payment_mode_id')->mapWithKeys(function ($group) {
            $modeName = optional($group->first()->mode)->name ?? 'Unknown';
            return [$modeName => formatCurrency($group->sum('paid_amount'))];
        });

        // Merge expected modes with actual summary
        $summary = $expectedModes->merge($summary);

        // Add City Ledger if applicable
        $cityLedgerTotal = $payments->where('balance', '>', 0)->sum('balance');
        $summary->put('City Ledger', formatCurrency($cityLedgerTotal));

        // Calculate and append total
        $totalPaid = $payments->sum('paid_amount');
        $summary->put('Total', formatCurrency($totalPaid));

        return response()->json($summary);
    }
}
