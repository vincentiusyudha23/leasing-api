<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeasingPlan extends Model
{
    use HasFactory;

    protected $table = 'leasing_plans';
    protected $fillable = ['name', 'max_training_session', 'max_date'];
}
