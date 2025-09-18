<?php
namespace App\Http\Controllers;

use App\Http\Requests\Quotation\StoreRequest;
use App\Models\Quotation;
use Illuminate\Support\Facades\Request;

class QuotationController extends Controller
{
    public function dropdownList()
    {
        $model = Quotation::query();
        $model->orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc");
        return $model->get();
    }

    public function index(Quotation $Quotation, Request $request)
    {
        return $Quotation->filterV1($request)->paginate(10);
    }

    public function store(StoreRequest $request)
    {
        try {
            $Quotation = Quotation::create($request->validated());

            return $this->response('Quotation successfully created.', $Quotation, true);
        } catch (\Exception $e) {

            return $this->response($e->getMessage(), null, false);
        }
    }
}
