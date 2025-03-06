<?php

namespace App\Models;

use App\Models\LeasingPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeasingPeriod extends Model
{
    use HasFactory;

    protected $table = 'leasing_periods';
    protected $fillable = ['device_id', 'leasing_plan_id', 'is_active', 'completed_trainings', 'leasing_next_check'];

    public function leasingPlan(): BelongsTo
    {
        return $this->belongsTo(LeasingPlan::class);
    }
}
