<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll(array $with = [])
    {
        return $this->model->with($with)->get();
    }

    public function getPaginated(int $perPage = 15, array $with = [])
    {
        return $this->model->with($with)->paginate($perPage);
    }

    public function findById($id, array $with = [])
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->findById($id);
        if ($record) {
            $record->update($data);
            return $record->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $record = $this->findById($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    public function findByField(string $field, $value, array $with = [])
    {
        return $this->model->with($with)->where($field, $value)->first();
    }
}
