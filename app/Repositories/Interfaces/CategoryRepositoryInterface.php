<?php

namespace App\Repositories\Interfaces;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find category by name
     *
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByName(string $name);
}
