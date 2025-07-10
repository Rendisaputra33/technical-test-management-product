<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'deskripsi',
    ];

    /**
     * Get the product locations for the location.
     */
    public function productLocations()
    {
        return $this->hasMany(ProductLocation::class);
    }

    /**
     * Get the products stored in this location.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_locations')
            ->withPivot('stok')
            ->withTimestamps();
    }
}
