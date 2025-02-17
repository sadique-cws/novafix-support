<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Problem extends BaseModel
{
    protected $guarded = [];

    public function model(): HasOne
    {
        return $this->hasOne(Model::class, 'id', 'model_id');
    }

    /**
     * Get all of the question for the Problem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'problem_id', 'id');
    }
}
