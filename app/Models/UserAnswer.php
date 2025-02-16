<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAnswer extends BaseModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'question_id', 'answer', 'device_id', 'brand_id', 'model_id', 'problem_id',"selected_answer"];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function device() {
        return $this->belongsTo(Device::class);
    }

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

    public function model() {
        return $this->belongsTo(Model::class);
    }

    public function problem() {
        return $this->belongsTo(Problem::class);
    }
}
