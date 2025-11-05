<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CasafariProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'casafari_id',
        'reference',
        'property_type',
        'listing_type',
        'status',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'price',
        'currency',
        'bedrooms',
        'bathrooms',
        'area_total',
        'area_built',
        'area_unit',
        'year_built',
        'description',
        'photos',
        'main_photo_url',
        'features',
        'raw_data',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'area_total' => 'decimal:2',
        'area_built' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'year_built' => 'integer',
        'photos' => 'array',
        'features' => 'array',
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->region,
            $this->postal_code,
            $this->country,
        ]));
    }

    /**
     * Scope to filter active properties.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by property type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope to filter by city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to filter by price range.
     */
    public function scopeInPriceRange($query, ?float $min = null, ?float $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }
}
