<?php

namespace App\Repositories\Interfaces;

interface ProductLocationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get stock by product and location
     *
     * @param string $productId
     * @param string $locationId
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getStock(string $productId, string $locationId);

    /**
     * Update stock
     *
     * @param string $productId
     * @param string $locationId
     * @param int $quantity
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateStock(string $productId, string $locationId, int $quantity);

    /**
     * Get all stock for a product
     *
     * @param string $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductStock(string $productId);

    /**
     * Get all stock for a location
     *
     * @param string $locationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLocationStock(string $locationId);
}
