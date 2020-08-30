<?php 
namespace Nfunwigabga\LaraRepo\Eloquent\Criteria;

use Nfunwigabga\LaraRepo\Base\ICriterion;

class EagerLoad implements ICriterion
{

    protected $relationships;

    public function __construct($relationships)
    {
        $this->relationships = $relationships;
    }

    public function apply($model)
    {
        return $model->with($this->relationships);
    }
}