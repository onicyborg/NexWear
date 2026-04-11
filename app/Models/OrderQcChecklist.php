<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BlameableTrait;
use App\Traits\SystemLoggable;

class OrderQcChecklist extends Model
{
    use HasFactory, HasUuids, BlameableTrait, SystemLoggable;

    protected $fillable = ['order_id', 'qc_category', 'qc_instruction', 'is_passed', 'notes', 'checked_by', 'checked_at', 'created_by', 'updated_by'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
