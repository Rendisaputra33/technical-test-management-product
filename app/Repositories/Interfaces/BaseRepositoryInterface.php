<?php

namespace App\Repositories\Interfaces;

interface BaseRepositoryInterface
{
    /**
     * Get all records with optional relationships
     *
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $with = []);

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $with
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $with = []);

    /**
     * Find record by ID
     *
     * @param string|int $id
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findById($id, array $with = []);

    /**
     * Create new record
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update record by ID
     *
     * @param string|int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function update($id, array $data);

    /**
     * Delete record by ID
     *
     * @param string|int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Find record by specific field
     *
     * @param string $field
     * @param mixed $value
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByField(string $field, $value, array $with = []);
}
