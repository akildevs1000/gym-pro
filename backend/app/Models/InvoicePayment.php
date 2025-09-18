<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_mode_id',
        'payment_mode_ref',
        'paid_amount',
        'balance', // 👈 Add this
    ];

}
