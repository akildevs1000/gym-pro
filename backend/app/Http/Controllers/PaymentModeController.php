<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentMode\StoreRequest;
use App\Http\Requests\PaymentMode\UpdateRequest;
use App\Models\PaymentMode;
use Illuminate\Http\Request;

class PaymentModeController extends Controller
{
    public function dropdownList()
    {
        return PaymentMode::orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc")->where("display_dropdown_list", true)->get();
    }

    public function index(PaymentMode $model, Request $request)
    {
        return $model->where("display_dropdown_list", false)->paginate($request->per_page);
    }

    public function store(StoreRequest $request)
    {

        try {
            $data = $request->validated();

            $record = PaymentMode::create($data);

            if ($record) {
                return $this->response('Payment Mode Successfully created.', $record, true);
            } else {
                return $this->response('PaymentMode cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateRequest $request, PaymentMode $PaymentMode)
    {
        try {
            $record = $PaymentMode->update($request->validated());
            return $this->response('Payment Mode successfully updated.', $record, true);
        } catch (\Exception $e) {
            return $this->response('Payment Mode cannot update.', null, false);
        }
    }

    public function destroy(PaymentMode $PaymentMode)
    {
        try {
            $PaymentMode->delete();
            return $this->response('Payment Mode deleted successfully .', null, true);
        } catch (\Exception $e) {
            return $this->response($e->getMessage(), null, false);
        }
    }
}
