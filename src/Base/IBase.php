<?php

namespace Nfunwigabga\LaraRepo\Base;

interface IBase 
{
    public function all();
    public function find($id);
    public function findOrFail($id);
    public function fill($id, array $data);
    public function findWhere($column, $value);
    public function findWhereFirst($column, $value);
    public function paginate($perPage = 10);
    public function create(array $data);
    public function update($id, array $data);
    public function updateOrCreate(array $condition, array $data);
    public function delete($id);

}