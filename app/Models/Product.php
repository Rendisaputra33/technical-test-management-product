<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';


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
        'kode_produk',
        'nama_produk',
        'kategori',
        'satuan',
        'deskripsi',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori');
    }

    /**
     * Get the product locations for the product.
     */
    public function productLocations()
    {
        return $this->hasMany(ProductLocation::class);
    }

    /**
     * Get the locations where this product is stored.
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'product_locations')
            ->withPivot('stok')
            ->withTimestamps();
    }
}
