<?php

namespace App\Repositories;

use App\Models\ProductLocation;
use App\Repositories\Interfaces\ProductLocationRepositoryInterface;

class ProductLocationRepository extends BaseRepository implements ProductLocationRepositoryInterface
{
    public function __construct(ProductLocation $model)
    {
        parent::__construct($model);
    }

    public function getStock(string $productId, string $locationId)
    {
        return $this->model->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->with(['product', 'location'])
            ->first();
    }

    public function updateStock(string $productId, string $locationId, int $quantity)
    {
        $stock = $this->getStock($productId, $locationId);

        if ($stock) {
            $stock->update(['stok' => $quantity]);
            return $stock->fresh(['product', 'location']);
        }

        return $this->create([
            'product_id' => $productId,
            'location_id' => $locationId,
            'stok' => $quantity
        ]);
    }

    public function getProductStock(string $productId)
    {
        return $this->model->where('product_id', $productId)
            ->with(['location', 'product'])
            ->get();
    }

    public function getLocationStock(string $locationId)
    {
        return $this->model->where('location_id', $locationId)
            ->with(['product', 'location'])
            ->get();
    }
}
