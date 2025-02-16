<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Brand extends Model
{
    protected $guarded = [];
    public function device(): HasOne
    {
        return $this->hasOne(Device::class, 'id', 'device_id');
    }
}
