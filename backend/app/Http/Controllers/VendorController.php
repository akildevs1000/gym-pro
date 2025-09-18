<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendor\StoreRequest;
use App\Http\Requests\Vendor\UpdateRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function dropdownList()
    {
        return Vendor::orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc")->get();
    }

    public function index(Vendor $model, Request $request)
    {
        return $model->paginate($request->per_page);
    }

    public function store(StoreRequest $request)
    {

        try {
            $data = $request->validated();

            $record = Vendor::create($data);

            if ($record) {
                return $this->response('Vendor Successfully created.', $record, true);
            } else {
                return $this->response('Vendor cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateRequest $request, Vendor $Vendor)
    {
        try {
            $record = $Vendor->update($request->validated());
            return $this->response('Vendor successfully updated.', $record, true);
        } catch (\Exception $e) {
            return $this->response('Vendor cannot update.', null, false);
        }
    }

    public function destroy(Vendor $Vendor)
    {
        try {
            $Vendor->delete();
            return $this->response('Vendor deleted successfully .', null, true);
        } catch (\Exception $e) {
            return $this->response('Vendor delete.', null, false);
        }
    }
}
