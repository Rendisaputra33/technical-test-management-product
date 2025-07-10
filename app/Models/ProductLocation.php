<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLocation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'product_id',
        'stok',
    ];

    /**
     * Get the product that owns the product location.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location that owns the product location.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the mutations for the product location.
     */
    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }
}
