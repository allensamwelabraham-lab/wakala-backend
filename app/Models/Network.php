<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'color',
        'float_balance',
    ];

    protected function casts(): array
    {
        return [
            'float_balance' => 'decimal:2',
        ];
    }

    // Biashara inayomiliki mtandao huu
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Miamala ya mtandao huu
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
