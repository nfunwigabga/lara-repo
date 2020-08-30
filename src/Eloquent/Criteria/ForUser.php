<?php 
namespace Nfunwigabga\LaraRepo\Eloquent\Criteria;

use Nfunwigabga\LaraRepo\Base\ICriterion;

class ForUser implements ICriterion
{

    protected $user_id;

    protected $user_field;

    public function __construct($user_field, $user_id)
    {
        $this->user_field = $user_field;
        $this->user_id = $user_id;
    }

    public function apply($model)
    {
        return $model->where($this->user_field, $this->user_id);
    }
    
}