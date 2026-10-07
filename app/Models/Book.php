<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'isbn',
        'publisher',
        'publication_year',
        'description',
        'price',
        'stock',
        'low_stock_threshold',
        'cover_image',
        'rating',
        'rating_count',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'rating_count' => 'integer',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_featured' => 'boolean',
    ];

    protected $appends = ['cover_image_url', 'is_low_stock'];

    public function getCoverImageUrlAttribute()
    {
        if (empty($this->cover_image)) {
            return 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=600';
        }

        $img = trim($this->cover_image);

        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }

        $cleanPath = ltrim(str_replace(['/storage/', 'storage/'], '', $img), '/\\');
        return url('storage/' . $cleanPath);
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock <= ($this->low_stock_threshold ?? 5);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class)->latest();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= COALESCE(low_stock_threshold, 5)');
    }
}
