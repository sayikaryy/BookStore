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
        'payment_method', // ABA_KHQR, ACLEDA_KHQR, BAKONG_KHQR, CARD, CASH_ON_DELIVERY
        'bank_provider',   // ABA, ACLEDA, BAKONG, STRIPE, CASH
        'amount',
        'currency',       // USD, KHR
        'status',         // pending, paid, failed, refunded
        'qr_string',
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
