<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\StoreRequest;
use App\Http\Requests\Expense\UpdateRequest;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function dropdownList()
    {
        return Expense::orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc")->get();
    }

    public function index(Request $request)
    {

        $query = Expense::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter: invoice_number (partial match)
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        // Filter: created_at date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        return $query->with("expense_category","vendor")->paginate($request->per_page ?? 15);
    }

    public function store(StoreRequest $request)
    {

        try {
            $data = $request->validated();

            $record = Expense::create($data);

            if ($record) {
                return $this->response('Expense Successfully created.', $record, true);
            } else {
                return $this->response('Expense cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateRequest $request, Expense $Expense)
    {
        try {
            $record = $Expense->update($request->validated());
            return $this->response('Expense successfully updated.', $record, true);
        } catch (\Exception $e) {
            return $this->response('Expense cannot update.', null, false);
        }
    }

    public function destroy(Expense $Expense)
    {
        try {
            $Expense->delete();
            return $this->response('Expense deleted successfully .', null, true);
        } catch (\Exception $e) {
            return $this->response('Expense delete.', null, false);
        }
    }
}
