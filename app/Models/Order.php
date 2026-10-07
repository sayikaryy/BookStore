<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'delivery_fee',
        'discount',
        'total_amount',
        'status', // pending, processing, shipped, delivered, cancelled
        'shipping_address',
        'delivery_method',
        'phone',
        'note',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected $appends = ['invoice_number'];

    public function getInvoiceNumberAttribute()
    {
        return $this->order_number ?: ('ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
