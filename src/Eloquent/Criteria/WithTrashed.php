<?php 
namespace Nfunwigabga\LaraRepo\Eloquent\Criteria;

use Nfunwigabga\LaraRepo\Base\ICriterion;

class WithTrashed implements ICriterion
{

    public function apply($model)
    {
        return $model->withTrashed();
    }
}