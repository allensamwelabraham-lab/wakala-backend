<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'business_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Biashara ya mtumiaji huyu
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Miamala aliyorekodi mtumiaji huyu
    public function recordedTransactions()
    {
        return $this->hasMany(Transaction::class, 'recorded_by');
    }

    // Je ni boss?
    public function isBoss(): bool
    {
        return $this->role === 'boss';
    }
}
