<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Brand extends BaseModel
{
    protected $guarded = [];
    public function device(): HasOne
    {
        return $this->hasOne(Device::class, 'id', 'device_id');
    }


    public function models(): HasMany
    {
        return $this->hasMany(Model::class, 'brand_id', 'id');
    }
}
