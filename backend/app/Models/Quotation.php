<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'items',
        'sub_total',
        'tax',
        'discount',
        'total',
        'notes',
    ];

    protected $casts = [
        'items'       => 'array',
        'sub_total'   => 'decimal:2',
        'tax'         => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
    ];

    protected $appends = [
        "quotation_number", 'datetime', "item_as_strings",
    ];

    public function getItemAsStringsAttribute($value)
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item["name"];
        }
        return implode(", ", $items);
    }

    public function getQuotationNumberAttribute()
    {
        return "QTN-" . $this->id;
    }

    public function getDateTimeAttribute()
    {
        return date("d-M-y H:i:s", strtotime($this->created_at));
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function filterV1()
    {
        $model = self::query();

        $model->with(["member"])
            ->when(request()->filled('search'), function ($q) {
                $q->where(function ($q) {
                    $searchTerm = request("search") . "%";
                    $q->where('id', env('WILD_CARD') ?? 'ILIKE', $searchTerm);
                });
            });

        return $model;
    }
}
