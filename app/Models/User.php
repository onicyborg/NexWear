<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BlameableTrait;
use App\Traits\SystemLoggable;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, BlameableTrait, SystemLoggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'created_by', 'updated_by'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function orders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function productionTrackings()
    {
        return $this->hasMany(ProductionTracking::class, 'processed_by');
    }

    public function orderQcChecklists()
    {
        return $this->hasMany(OrderQcChecklist::class, 'checked_by');
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class, 'user_id');
    }
}
