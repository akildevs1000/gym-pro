<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'attachments' => 'array',
        "created_at" => "datetime:d-M-y"
    ];

    protected $appends = ["date", "time", "datetime", "show_invoice_date"];

    public function expense_category()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withDefault(function ($vendor) {
            $vendor->first_name = '---';
            $vendor->last_name  = '';
        });
    }


    public function payments()
    {
        return $this->hasMany(PaymentMode::class, "name", "payment_mode");
    }

    public function getDateAttribute()
    {
        return date("d-M-y", strtotime($this->created_at));
    }

    public function getTimeAttribute()
    {
        return date("H:i", strtotime($this->created_at));
    }

    public function getDatetimeAttribute()
    {
        return date("d-M-y H:i", strtotime($this->created_at));
    }

    public function getShowInvoiceDateAttribute()
    {
        return date("d-M-Y", strtotime($this->invoice_date));
    }
}
