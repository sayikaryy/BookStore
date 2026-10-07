<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'properties',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record($action, $module, $description, array $properties = [], $userId = null)
    {
        try {
            return self::create([
                'user_id' => $userId ?? (Auth::check() ? Auth::id() : null),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => Request::ip(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ActivityLog failed: ' . $e->getMessage());
            return null;
        }
    }
}
