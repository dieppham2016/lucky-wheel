<?php


namespace App\Nkid\ModelLogic\Wheel;


use App\Models\Wheel\WheelLog;
use App\Nkid\ModelLogic\BaseModelLogic;

class GameLogModelLogic extends BaseModelLogic
{
    const S_PENDING = 'PENDING';
    const S_COMPLETE = 'COMPLETE';

    protected $searchColumn = ['user_name', 'created_at', 'updated_at'];

    public function __construct($module = null) {
        $this->newQuery();
        $this->getModelReference();
    }

    public function newQuery() {
        $this->query = WheelLog::query();
    }

    public function getModelReference() {
        $model = new WheelChangeLog();
        $this->fillable = $model->getFillable();
        unset($model);
    }

}
