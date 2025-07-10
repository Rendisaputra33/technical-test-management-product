<?php

namespace App\Repositories\Interfaces;

interface LocationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find location by code
     *
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByCode(string $code);
}
