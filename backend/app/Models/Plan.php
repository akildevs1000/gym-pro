<?php
namespace App\Models;

use App\Models\Community\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'features' => 'array',
        'price'    => 'decimal:2',
    ];

    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_plan')
            ->withPivot([
                'plan_start_date',
                'plan_end_date',
                'discount',
                'after_discount',
                'total',
                'status',
            ])
            ->withTimestamps();
    }
}
