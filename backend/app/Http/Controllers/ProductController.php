<?php
namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ProductController extends Controller
{
    public function dropdownList()
    {
        $model = Product::query();
        $model->orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc");
        return $model->get();
    }

    public function index(Product $Product, Request $request)
    {
        return $Product->filterV1($request)->paginate(10);
    }

    public function store(StoreRequest $request)
    {
        $payload = collect($request->validated())->map(function ($value) {
            return ($value === 'null' || $value === '') ? null : $value;
        });

        // Handle profile picture upload
        if ($request->hasFile('image')) {
            $payload['image'] = $this->uploadProfilePicture($request->file('image'));
        }

        DB::beginTransaction();

        try {
            $Product = Product::create($payload->toArray());

            DB::commit();

            $Product->image = asset('media/product/image/' . $Product->image);

            return $this->response('Product successfully created.', $Product, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response($e->getMessage(), null, false);
        }
    }

    public function productUpdate(UpdateRequest $request, $id)
    {
        $data = $request->validated();

        $Product = Product::where("id", $id)->first();

        if ($request->image && $request->hasFile('image')) {
            $file     = $request->file('image');
            $ext      = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->image->move(public_path('media/product/image/'), $fileName);
            $data['image'] = $fileName;
        }

        try {
            $updated = $Product->update($data);
            if (! $updated) {
                return $this->response('Product cannot update.', null, false);
            }

            return $this->response('Product Details successfully updated.', $Product, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Product $Product)
    {
        try {
            $Product->delete();

            return response()->json(['message' => 'Product successfully deleted.', 'status' => true], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function uploadProfilePicture($file)
    {
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('media/product/image/'), $fileName);
        return $fileName;
    }

}
