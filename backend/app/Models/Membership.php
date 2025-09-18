<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_EXPIRED = 0;

    protected $fillable = [
        'member_id',
        'plan_id',
        'plan_start_date',
        'plan_end_date',
        'status',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get all of the payments for the Membership
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class,"plan_id","plan_id");
    }

    protected $appends = [
        'show_plan_start_date',
        'edit_plan_start_date',
        'show_plan_end_date',
        'edit_plan_end_date',
    ];

    public function getShowPlanStartDateAttribute(): string
    {
        return date('d M Y', strtotime($this->plan_start_date));
    }

    public function getEditPlanStartDateAttribute(): string
    {
        return date('Y-m-d', strtotime($this->plan_start_date));
    }

    public function getShowPlanEndDateAttribute(): string
    {
        return date('d M Y', strtotime($this->plan_end_date));
    }

    public function getEditPlanEndDateAttribute(): string
    {
        return date('Y-m-d', strtotime($this->plan_end_date));
    }
}
