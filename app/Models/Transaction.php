<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'recorded_by',
        'network_id',
        'type',
        'amount',
        'commission',
        'cash_after',
        'float_after',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'type'        => TransactionType::class,
            'amount'      => 'decimal:2',
            'commission'  => 'decimal:2',
            'cash_after'  => 'decimal:2',
            'float_after' => 'decimal:2',
        ];
    }

    // Biashara inayomiliki muamala huu
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Mtumiaji aliyerekodi muamala huu
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Mtandao wa muamala huu (huenda null)
    public function network()
    {
        return $this->belongsTo(Network::class);
    }
}
