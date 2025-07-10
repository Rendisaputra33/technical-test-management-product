<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_location_id',
        'tanggal',
        'jenis_mutasi',
        'jumlah',
        'keterangan',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Get the product location that owns the mutation.
     */
    public function productLocation()
    {
        return $this->belongsTo(ProductLocation::class);
    }

    /**
     * Get the product through the product location.
     */
    public function product()
    {
        return $this->hasOneThrough(Product::class, ProductLocation::class, 'id', 'id', 'product_location_id', 'product_id');
    }

    /**
     * Get the location through the product location.
     */
    public function location()
    {
        return $this->hasOneThrough(Location::class, ProductLocation::class, 'id', 'id', 'product_location_id', 'location_id');
    }

    /**
     * Scope a query to only include 'masuk' mutations.
     */
    public function scopeMasuk($query)
    {
        return $query->where('jenis_mutasi', 'masuk');
    }

    /**
     * Scope a query to only include 'keluar' mutations.
     */
    public function scopeKeluar($query)
    {
        return $query->where('jenis_mutasi', 'keluar');
    }
}
