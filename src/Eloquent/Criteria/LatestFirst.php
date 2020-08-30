<?php 
namespace Nfunwigabga\LaraRepo\Eloquent\Criteria;

use Nfunwigabga\LaraRepo\Base\ICriterion;

class LatestFirst implements ICriterion
{

    public function apply($model)
    {
        return $model->latest();
    }
    
}