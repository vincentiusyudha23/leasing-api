<?php

namespace App\Models;

use App\Models\User;
use App\Models\LeasingPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';
    protected $fillable = ['user_id', 'uniq_id', 'device_type', 'activation_code', 'api_key', 'registration_date'];
    protected $appends = ['device_owner_details', 'leasing_period'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leasing_period(): HasMany
    {
        return $this->hasMany(LeasingPeriod::class);
    }

    public function getDeviceOwnerDetailsAttribute()
    {
        return [
            'billing_name' => $this->user->name,
            'address_country' => $this->user->address_country,
            'address_zip' => $this->user->address_zip,
            'address_city' => $this->user->address_city,
            'address_street' => $this->user->address_street,
            'vat_number' =>$this->user->vat_number
        ];
    }
}
