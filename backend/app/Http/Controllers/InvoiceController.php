<?php
namespace App\Http\Controllers;

use App\Http\Requests\Invoice\PaymentRequest;
use App\Http\Requests\Invoice\StoreRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Request;

class InvoiceController extends Controller
{
    public function dropdownList()
    {
        $model = Invoice::query();
        $model->orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc");
        return $model->get();
    }

    public function index(Invoice $Invoice, Request $request)
    {
        return $Invoice->filterV1($request)->paginate(10);
    }

    public function counts(Invoice $invoice, Request $request)
    {
        $query = $invoice->filterV1($request);

        $totals = $query->selectRaw('
        SUM(total) as total_sum,
        SUM(balance) as balance_sum,
        SUM(total - balance) as profit_or_loss
    ')->first();

        $data = [
            [
                "title"    => "Total",
                "subtitle" => $totals->total_sum,
                "icon"     => "mdi-cash-multiple", // Money icon
                "color"    => "#66BB6A",
            ],
            [
                "title"    => "Pending Balance",
                "subtitle" => $totals->balance_sum,
                "icon"     => "mdi-cash-clock", // Pending or due payment
                "color"    => "#FFA726",
            ],
            // [
            //     "title"    => "Profit / Loss",
            //     "subtitle" => $totals->profit_or_loss,
            //     "icon"     => "mdi-chart-line", // Financial trend
            //     "color"    => "#AB47BC",
            // ],
        ];

        return $data;
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        try {
            if (! $request->payment_mode_id) {
                $data["payment_mode_id"] = 0;
            }

            if (! $request->paid_amount || $request->paid_amount == 0) {
                $data["paid_amount"] = 0;
                $data["balance"]     = $data["total"] - $data["paid_amount"];
            }
            $Invoice = Invoice::create($data);

            return $this->response('Invoice successfully created.', $Invoice, true);
        } catch (\Exception $e) {

            return $this->response($e->getMessage(), null, false);
        }
    }

    public function payment(PaymentRequest $request)
    {
        try {
            $validated = $request->validated();

            $invoice = Invoice::findOrFail($validated['invoice_id']);

            $remaining_balance = $invoice->balance - $validated['paid_amount'];
            $paid_amount = $invoice->paid_amount + $validated['paid_amount'];

            InvoicePayment::create([
                 ...$validated,
                'balance' => $remaining_balance,
            ]);

            // Update invoice balance
            $invoice->balance = $remaining_balance;
            $invoice->paid_amount = $paid_amount;
            $invoice->save();

            return $this->response('Payment recorded successfully.', null, true);
        } catch (\Exception $e) {

            return $this->response($e->getMessage(), null, false);
        }

    }

}
