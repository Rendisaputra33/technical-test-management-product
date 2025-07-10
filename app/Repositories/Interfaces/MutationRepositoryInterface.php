<?php

namespace App\Repositories\Interfaces;

interface MutationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get mutations by type
     *
     * @param string $type
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType(string $type, array $filters = []);

    /**
     * Get mutations by product location
     *
     * @param int $productLocationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByProductLocation(int $productLocationId);

    /**
     * Get mutations by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange(string $startDate, string $endDate);

    /**
     * Get stock report
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockReport(array $filters = []);

    /**
     * Get mutations by user with pagination
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $userId, int $perPage = 15);

    /**
     * Get mutations by product with pagination
     *
     * @param int $productId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByProduct(string $productId, int $perPage = 15);
}
