<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get products with category and stock information
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductsWithStock(array $filters = []);

    /**
     * Find product by code
     *
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByCode(string $code);

    /**
     * Get products by category
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCategory(int $categoryId);

    /**
     * Search products by name or code
     *
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchProducts(string $search);
}
