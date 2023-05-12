<?php

namespace LeafyTech\Core\Repositories;

interface RepositoryInterface
{
    public function all($columns = ['*']);

    public function find($id, $columns = ['*']);

    public function update(int $id, array $attributes);

    public function delete(int $id);

    public function insert(array $attributes);

    public function updateOrInsert(array $attributes, array $values = []);

}