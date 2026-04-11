<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BlameableTrait;
use App\Traits\SystemLoggable;

class MasterQcKpi extends Model
{
    use HasFactory, HasUuids, BlameableTrait, SystemLoggable;

    protected $fillable = ['category', 'instruction', 'is_active', 'created_by', 'updated_by'];
    public $incrementing = false;
    protected $keyType = 'string';
}
