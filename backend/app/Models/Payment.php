<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'balance'     => 'decimal:2',
        'created_at'  => 'datetime:d-M-y H:i:s',
    ];

    protected $appends = [
        'datetime',
    ];

    public function getDateTimeAttribute()
    {
        return date("d-M-y H:i:s", strtotime($this->created_at));
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function mode() // optional helper
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id');
    }
}
