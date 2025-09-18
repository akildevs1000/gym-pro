<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d-M-y',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'image_raw',
        'image_base64',
    ];

    public function getImageAttribute($value)
    {
        if (! $value) {
            return null;
        }

        return asset('media/product/image/' . $value);
        // return asset(env('BUCKET_URL') . '/' . $value);

    }

    public function getImageBase64Attribute()
    {
        return null;
        if ($this->image) {
            $imageData = file_get_contents($this->image);

            $md5string = base64_encode($imageData);

            return "data:image/png;base64,$md5string";
        }

        return null;
    }

    public function getImageRawAttribute()
    {
        // Ensure image exists and is not empty
        if (empty($this->image)) {
            return ''; // Return an empty string if image is not set
        }

        // Split the path string
        $arr = explode('media/product/image/', $this->image);

        return isset($arr[1]) ? $arr[1] : '';
    }

    public function getCreatedAtAttribute($value): string
    {
        return date('d M Y', strtotime($value));
    }
    public function filterV1()
    {
        $model = self::query();

        $model
            ->when(request()->filled('search'), function ($q) {
                $q->where(function ($q) {
                    $searchTerm = request("search") . "%";
                    $q->where('name', env('WILD_CARD') ?? 'ILIKE', $searchTerm)
                        ->orWhere('product_category_id', env('WILD_CARD') ?? 'ILIKE', $searchTerm);
                });
            });

        return $model;
    }
}
