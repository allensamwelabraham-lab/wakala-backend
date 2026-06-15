<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Business;
use App\Models\Network;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    // Rekodi muamala wa kawaida
    public function record(
        Business $business,
        User $recorder,
        TransactionType $type,
        float $amount,
        ?Network $network = null,
        float $commission = 0,
        ?string $note = null,
    ): Transaction {
        return DB::transaction(function () use ($business, $recorder, $type, $amount, $network, $commission, $note) {

            // Hesabu salio jipya la cash
            $newCash = $business->cash_balance + ($type->cashDirection() * $amount);

            if ($newCash < 0) {
                throw ValidationException::withMessages([
                    'amount' => "Cash haitoshi. Unayo " . number_format($business->cash_balance) . " TZS tu.",
                ]);
            }

            $business->update(['cash_balance' => $newCash]);

            // Float — tu kama kuna mtandao
            $newFloat = null;
            if ($network) {
                $newFloat = $network->float_balance + ($type->floatDirection() * $amount);

                if ($newFloat < 0) {
                    throw ValidationException::withMessages([
                        'amount' => "Float ya {$network->name} haitoshi. Ina " . number_format($network->float_balance) . " TZS tu.",
                    ]);
                }

                $network->update(['float_balance' => $newFloat]);
            }

            return Transaction::create([
                'business_id' => $business->id,
                'recorded_by' => $recorder->id,
                'network_id'  => $network?->id,
                'type'        => $type,
                'amount'      => $amount,
                'commission'  => $commission,
                'cash_after'  => $newCash,
                'float_after' => $newFloat,
                'note'        => $note,
            ]);
        });
    }

    // Futa muamala (rudisha salio, kisha soft delete)
    public function reverse(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $business = $transaction->business;
            $network  = $transaction->network;
            $type     = $transaction->type;

            if ($type->isLipaNamba()) {
                // Rudisha mantiki ya Lipa Namba
                if ($type === TransactionType::LIPA_NAMBA_SALE) {
                    $cashGiven = $transaction->amount - $transaction->commission;
                    $business->update([
                        'cash_balance'       => $business->cash_balance + $cashGiven,
                        'lipa_namba_balance' => $business->lipa_namba_balance - $transaction->amount,
                    ]);
                } else {
                    $business->update([
                        'cash_balance'       => $business->cash_balance - $transaction->amount,
                        'lipa_namba_balance' => $business->lipa_namba_balance + $transaction->amount,
                    ]);
                }
            } else {
                $business->update([
                    'cash_balance' => $business->cash_balance - ($type->cashDirection() * $transaction->amount),
                ]);

                if ($network) {
                    $network->update([
                        'float_balance' => $network->float_balance - ($type->floatDirection() * $transaction->amount),
                    ]);
                }
            }

            $transaction->delete();
        });
    }

    // Hariri muamala wa kawaida
    public function update(
        Transaction $transaction,
        TransactionType $type,
        float $amount,
        float $commission = 0,
        ?string $note = null,
    ): Transaction {
        return DB::transaction(function () use ($transaction, $type, $amount, $commission, $note) {
            $business = $transaction->business;
            $network  = $transaction->network;   // huenda ni null (cash)
            $oldType  = $transaction->type;

            // 1. Rudisha athari ya zamani
            $business->update([
                'cash_balance' => $business->cash_balance - ($oldType->cashDirection() * $transaction->amount),
            ]);

            if ($network) {
                $network->update([
                    'float_balance' => $network->float_balance - ($oldType->floatDirection() * $transaction->amount),
                ]);
            }

            // 2. Weka athari mpya
            $newCash = $business->fresh()->cash_balance + ($type->cashDirection() * $amount);

            if ($newCash < 0) {
                throw ValidationException::withMessages([
                    'amount' => "Cash haitoshi kwa mabadiliko haya.",
                ]);
            }

            $business->update(['cash_balance' => $newCash]);

            $newFloat = null;
            if ($network) {
                $newFloat = $network->fresh()->float_balance + ($type->floatDirection() * $amount);

                if ($newFloat < 0) {
                    throw ValidationException::withMessages([
                        'amount' => "Float ya {$network->name} haitoshi kwa mabadiliko haya.",
                    ]);
                }

                $network->update(['float_balance' => $newFloat]);
            }

            // 3. Sasisha muamala
            $transaction->update([
                'type'        => $type,
                'amount'      => $amount,
                'commission'  => $commission,
                'cash_after'  => $newCash,
                'float_after' => $newFloat,
                'note'        => $note,
            ]);

            return $transaction;
        });
    }

    // Rekodi muamala wa Lipa Namba (mantiki ya namba mbili)
    public function recordLipaNamba(
        Business $business,
        User $recorder,
        TransactionType $type,
        float $amount,
        float $commission = 0,
        ?string $note = null,
    ): Transaction {
        return DB::transaction(function () use ($business, $recorder, $type, $amount, $commission, $note) {

            if ($type === TransactionType::LIPA_NAMBA_SALE) {
                $cashGiven = $amount - $commission;

                if ($cashGiven > $business->cash_balance) {
                    throw ValidationException::withMessages([
                        'amount' => "Cash haitoshi. Unayo " . number_format($business->cash_balance) . " TZS tu.",
                    ]);
                }

                $business->update([
                    'cash_balance'       => $business->cash_balance - $cashGiven,
                    'lipa_namba_balance' => $business->lipa_namba_balance + $amount,
                ]);
            } else {
                if ($amount > $business->lipa_namba_balance) {
                    throw ValidationException::withMessages([
                        'amount' => "Lipa Namba haina kiasi hicho. Ina " . number_format($business->lipa_namba_balance) . " TZS tu.",
                    ]);
                }

                $business->update([
                    'lipa_namba_balance' => $business->lipa_namba_balance - $amount,
                    'cash_balance'       => $business->cash_balance + $amount,
                ]);
            }

            $fresh = $business->fresh();

            return Transaction::create([
                'business_id' => $business->id,
                'recorded_by' => $recorder->id,
                'network_id'  => null,
                'type'        => $type,
                'amount'      => $amount,
                'commission'  => $commission,
                'cash_after'  => $fresh->cash_balance,
                'float_after' => null,
                'note'        => $note,
            ]);
        });
    }

    // Hariri muamala wa Lipa Namba
    public function updateLipaNamba(
        Transaction $transaction,
        float $amount,
        float $commission = 0,
        ?string $note = null,
    ): Transaction {
        return DB::transaction(function () use ($transaction, $amount, $commission, $note) {
            $business = $transaction->business;
            $type     = $transaction->type;

            // 1. RUDISHA athari ya zamani
            if ($type === TransactionType::LIPA_NAMBA_SALE) {
                $oldCashGiven = $transaction->amount - $transaction->commission;
                $business->update([
                    'cash_balance'       => $business->cash_balance + $oldCashGiven,
                    'lipa_namba_balance' => $business->lipa_namba_balance - $transaction->amount,
                ]);
            } else {
                $business->update([
                    'cash_balance'       => $business->cash_balance - $transaction->amount,
                    'lipa_namba_balance' => $business->lipa_namba_balance + $transaction->amount,
                ]);
            }

            // 2. WEKA athari mpya
            $business = $business->fresh();
            if ($type === TransactionType::LIPA_NAMBA_SALE) {
                $newCashGiven = $amount - $commission;
                if ($newCashGiven > $business->cash_balance) {
                    throw ValidationException::withMessages([
                        'amount' => "Cash haitoshi. Unayo " . number_format($business->cash_balance) . " TZS tu.",
                    ]);
                }
                $business->update([
                    'cash_balance'       => $business->cash_balance - $newCashGiven,
                    'lipa_namba_balance' => $business->lipa_namba_balance + $amount,
                ]);
            } else {
                if ($amount > $business->lipa_namba_balance) {
                    throw ValidationException::withMessages([
                        'amount' => "Lipa Namba haina kiasi hicho. Ina " . number_format($business->lipa_namba_balance) . " TZS tu.",
                    ]);
                }
                $business->update([
                    'lipa_namba_balance' => $business->lipa_namba_balance - $amount,
                    'cash_balance'       => $business->cash_balance + $amount,
                ]);
            }

            // 3. SASISHA muamala
            $transaction->update([
                'amount'     => $amount,
                'commission' => $commission,
                'cash_after' => $business->fresh()->cash_balance,
                'note'       => $note,
            ]);

            return $transaction;
        });
    }
}
