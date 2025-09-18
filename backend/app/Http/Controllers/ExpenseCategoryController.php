<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCategory\StoreRequest;
use App\Http\Requests\ExpenseCategory\UpdateRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function dropdownList()
    {
        return ExpenseCategory::orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc")->get();
    }

    public function index(ExpenseCategory $model, Request $request)
    {
        return $model->paginate($request->per_page);
    }

    public function store(StoreRequest $request)
    {

        try {
            $data = $request->validated();

            $record = ExpenseCategory::create($data);

            if ($record) {
                return $this->response('Expense Category Successfully created.', $record, true);
            } else {
                return $this->response('Expense Category cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateRequest $request, ExpenseCategory $ExpenseCategory)
    {
        try {
            $record = $ExpenseCategory->update($request->validated());
            return $this->response('Expense Category successfully updated.', $record, true);
        } catch (\Exception $e) {
            return $this->response('Expense Category cannot update.', null, false);
        }
    }

    public function destroy(ExpenseCategory $ExpenseCategory)
    {
        try {
            $ExpenseCategory->delete();
            return $this->response('Expense Category deleted successfully .', null, true);
        } catch (\Exception $e) {
            return $this->response('Expense Category delete.', null, false);
        }
    }
}
