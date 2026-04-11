<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BlameableTrait;

class Customer extends Model
{
    use HasFactory, HasUuids, BlameableTrait;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'address',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
