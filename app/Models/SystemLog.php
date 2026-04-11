<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SystemLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['user_id', 'action', 'table_name', 'record_id', 'old_values', 'new_values', 'method', 'url', 'request_payload', 'ip_address'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
