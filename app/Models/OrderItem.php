<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\SystemLoggable;

class OrderItem extends Model
{
    use HasFactory, HasUuids, SystemLoggable;

    protected $fillable = ['order_id', 'color_code', 'color_name', 'size', 'quantity'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
