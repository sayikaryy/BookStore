<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_method', // BAKONG_KHQR, CASH_ON_DELIVERY
        'bank_provider',  // BAKONG, CASH
        'amount',
        'currency',       // USD, KHR
        'status',         // pending, paid, failed, refunded
        'qr_string',
        'md5_hash',
        'deeplink',
        'qr_image_url',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
