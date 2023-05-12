<?php

namespace LeafyTech\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use LeafyTech\Core\Exception\AttributeNotFoundException;

class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    protected string $classModel;

    public function __construct()
    {
        $this->model = new $this->classModel;
    }

    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    public function update(int $id, array $attributes): bool
    {
        $model = $this->model
            ->where($this->model->getKeyName(), $id)
            ->firstOrFail();

        return $model->update($attributes);
    }

    public function delete(int $id)
    {
        return $this->model
            ->where($this->model->getKeyName(), $id)
            ->delete();
    }

    public function insert(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function updateOrInsert(array $attributes, array $values = [])
    {
        if(empty($attributes)) {
            throw new AttributeNotFoundException();
        }
        return $this->model->updateOrInsert($attributes, $values);
    }
}