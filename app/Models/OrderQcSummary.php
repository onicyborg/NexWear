<?php

namespace App\Models;

use App\Traits\BlameableTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderQcSummary extends Model
{
    use HasFactory, HasUuids, BlameableTrait;

    protected $fillable = [
        'order_id',
        'qty_pass',
        'qty_rework',
        'qty_reject',
        'general_notes',
        'created_by',
        'updated_by',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
