<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Problem extends BaseModel
{
    protected $guarded = [];

    public function model(): HasOne
    {
        return $this->hasOne(Model::class, 'id', 'model_id');
    }
}
