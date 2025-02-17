<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model As BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Model extends BaseModel
{
    protected $guarded = [];

    public function brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class, 'model_id', 'id');
    }

}
