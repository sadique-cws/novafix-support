<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Device extends Model
{
    protected $guarded = [];

    /**
     * Get all of the comments for the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }
}
