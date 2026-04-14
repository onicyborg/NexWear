<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BlameableTrait;
use App\Traits\SystemLoggable;

class Order extends Model
{
    use HasFactory, HasUuids, BlameableTrait, SystemLoggable;

    protected $fillable = ['order_no', 'po_number', 'customer_id', 'export_date', 'destination_country', 'ship_mode', 'status', 'created_by', 'updated_by'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'export_date' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function productionTrackings()
    {
        return $this->hasMany(ProductionTracking::class, 'order_id');
    }

    public function orderQcChecklists()
    {
        return $this->hasMany(OrderQcChecklist::class, 'order_id');
    }

    public function qcSummary()
    {
        return $this->hasOne(OrderQcSummary::class, 'order_id');
    }
}
