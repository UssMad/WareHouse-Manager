<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'name', 'reference', 'description',
        'unit_price', 'stock_quantity', 'alert_threshold',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'alert_threshold' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('occurred_at');
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity === 0) {
            return 'Rupture';
        }

        return $this->stock_quantity <= $this->alert_threshold ? 'À surveiller' : 'Disponible';
    }
}
