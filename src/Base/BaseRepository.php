<?php

namespace Nfunwigabga\LaraRepo\Base;

use Illuminate\Support\Arr;
use Nfunwigabga\LaraRepo\Exceptions\ModelNotDefined;
use Nfunwigabga\LaraRepo\Base\IBase;
use Nfunwigabga\LaraRepo\Base\ICriteria;

abstract class BaseRepository implements IBase, ICriteria
{

    protected $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    public function all()
    {
        return $this->model->get();
    }

    public function find($id)
    {
        $result = $this->model->find($id);
        return $result;
    }

    public function findOrFail($id)
    {
        $result = $this->model->findOrFail($id);
        return $result;
    }

    public function fill($id, array $data)
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();
        return $record;
    }

    public function findWhere($column, $value)
    {
        return $this->model->where($column, $value)->get();
    }

    public function findWhereFirst($column, $value)
    {
        return $this->model->where($column, $value)->firstOrFail();
    }

    public function paginate($perPage = 10)
    {
        return $this->model->paginate($perPage);
    }

    public function create(array $data)
    {
        $result = $this->model->create($data);
        return $result;
    }

    public function createForCurrentUser($relationship, array $data)
    {
        if(!auth()->check()){
            return false;
        }

        $record = auth()->user()->{$relationship}()->create($data);

        return $record;
    }

    public function createRelated($modelInstance, $relationship, array $data)
    {
        $record = $modelInstance->{$relationship}()->create($data);
        return $record;
    }

    public function update($id, array $data)
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function updateOrCreate(array $condition, array $data)
    {
        return $this->model->updateOrCreate($condition, $data);
    }

    public function delete($id)
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }


    public function withCriteria(...$criteria)
    {
        $criteria = Arr::flatten($criteria);

        foreach($criteria as $criterion){
            $this->model = $criterion->apply($this->model);
        }

        return $this;
    }



    protected function getModelClass()
    {
        if( !method_exists($this, 'model'))
        {
            throw new ModelNotDefined();
        }

        return app()->make($this->model());

    }

    


    
}