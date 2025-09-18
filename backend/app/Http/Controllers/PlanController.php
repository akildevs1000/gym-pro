<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plan\StoreRequest;
use App\Http\Requests\Plan\UpdateRequest;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function dropdownList()
    {
        return Plan::orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc")->get();
    }

    public function index(Plan $model, Request $request)
    {
        return $model->paginate($request->per_page);
    }

    public function store(StoreRequest $request)
    {

        try {
            $data = $request->validated();

            $record = Plan::create($data);

            if ($record) {
                return $this->response('Plan Successfully created.', $record, true);
            } else {
                return $this->response('Plan cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateRequest $request, Plan $Plan)
    {
        try {
            $record = $Plan->update($request->validated());
            return $this->response('Plan successfully updated.', $record, true);
        } catch (\Exception $e) {
            return $this->response('Plan cannot update.', null, false);
        }
    }

    public function destroy(Plan $Plan)
    {
        try {
            $Plan->delete();
            return $this->response('Plan deleted successfully .', null, true);
        } catch (\Exception $e) {
            return $this->response('Plan delete.', null, false);
        }
    }
}
