<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BlameableTrait;
use App\Traits\SystemLoggable;

class ProductionTracking extends Model
{
    use HasFactory, HasUuids, BlameableTrait, SystemLoggable;

    protected $fillable = ['order_id', 'status', 'processed_by', 'notes', 'started_at', 'completed_at', 'created_by', 'updated_by'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
