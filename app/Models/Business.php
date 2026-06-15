<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'cash_balance',
        'lipa_namba_balance',
    ];

    protected function casts(): array
    {
        return [
            'cash_balance' => 'decimal:2',
            'lipa_namba_balance' => 'decimal:2',
        ];
    }

    // Watumiaji wa biashara hii (boss + wafanyakazi)
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Mitandao ya biashara hii
    public function networks()
    {
        return $this->hasMany(Network::class);
    }

    // Miamala ya biashara hii
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Jumla ya float ya mitandao yote
    public function totalFloat(): float
    {
        return (float) $this->networks()->sum('float_balance');
    }
}
