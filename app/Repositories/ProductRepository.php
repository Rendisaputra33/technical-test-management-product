<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getProductsWithStock(array $filters = [])
    {
        $query = $this->model->with(['category', 'productLocations.location']);

        if (isset($filters['category_id'])) {
            $query->where('kategori', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_produk', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_produk', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get();
    }

    public function findByCode(string $code)
    {
        return $this->findByField('kode_produk', $code, ['category', 'productLocations.location']);
    }

    public function getByCategory(int $categoryId)
    {
        return $this->model->where('kategori', $categoryId)
            ->with(['category', 'productLocations.location'])
            ->get();
    }

    public function searchProducts(string $search)
    {
        return $this->model->where('nama_produk', 'like', '%' . $search . '%')
            ->orWhere('kode_produk', 'like', '%' . $search . '%')
            ->with(['category', 'productLocations.location'])
            ->get();
    }
}
