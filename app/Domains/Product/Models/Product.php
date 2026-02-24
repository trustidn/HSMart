<?php

namespace App\Domains\Product\Models;

use App\Domains\Purchasing\Models\PurchaseItem;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use BelongsToTenant, HasFactory;

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'sku',
        'barcode',
        'name',
        'cost_price',
        'sell_price',
        'stock',
        'minimum_stock',
        'is_active',
        'image_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'stock' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->minimum_stock > 0 && $this->stock <= $this->minimum_stock;
    }

    /**
     * Purchase items (purchases that included this product).
     *
     * @return HasMany<PurchaseItem, $this>
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->exists($this->image_path)
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }
}
