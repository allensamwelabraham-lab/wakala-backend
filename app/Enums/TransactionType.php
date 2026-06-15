<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEPOSIT        = 'weka';   // Mteja anaweka pesa
    case WITHDRAWAL     = 'toa';    // Mteja anatoa pesa
    case VOUCHER        = 'vocha';  // Mauzo ya vocha
    case ELECTRICITY    = 'umeme';  // Mauzo ya umeme
    case FLOAT_PURCHASE = 'float';  // Kununua float
    case CASH_IN     = 'cash_in';     // Ongeza cash (kipato cha nje)
    case CASH_OUT    = 'cash_out';    // Toa cash (matumizi)
    case FLOAT_IN    = 'float_in';    // Ongeza float (kamishini ya mtandao)
    case FLOAT_OUT   = 'float_out';   // Punguza float (marekebisho)
    case LIPA_NAMBA_SALE       = 'lipa_sale';        // Mauzo: mteja analipia, unampa cash
    case LIPA_NAMBA_SETTLEMENT = 'lipa_settlement';  // Kutoa benki (settlement)
    // Jina la Kiswahili la kila aina (kwa kuonyesha kwenye screen)
    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT        => 'Kuweka pesa',
            self::WITHDRAWAL     => 'Kutoa pesa',
            self::VOUCHER        => 'Vocha',
            self::ELECTRICITY    => 'Umeme',
            self::FLOAT_PURCHASE => 'Nunua Float',
            self::CASH_IN        => 'Ongeza Cash',
            self::CASH_OUT       => 'Toa Cash',
            self::FLOAT_IN       => 'Ongeza Float',
            self::FLOAT_OUT      => 'Punguza Float',
            self::LIPA_NAMBA_SALE       => 'Mauzo ya Lipa Namba',
            self::LIPA_NAMBA_SETTLEMENT => 'Settlement (Kutoa Benki)',
        };
    }

    // Cash inaongezeka (+1) au inapungua (-1)?
    public function cashDirection(): int
    {
        return match ($this) {
            self::DEPOSIT, self::VOUCHER, self::ELECTRICITY, self::CASH_IN => 1,
            self::WITHDRAWAL, self::FLOAT_PURCHASE, self::CASH_OUT         => -1,
            self::FLOAT_IN, self::FLOAT_OUT                                => 0,
            self::LIPA_NAMBA_SALE, self::LIPA_NAMBA_SETTLEMENT             => 0,
        };
    }

    // Float ni kinyume cha cash kila wakati
    public function floatDirection(): int
    {
        return match ($this) {
            self::WITHDRAWAL, self::FLOAT_PURCHASE, self::FLOAT_IN => 1,
            self::DEPOSIT, self::VOUCHER, self::ELECTRICITY, self::FLOAT_OUT => -1,
            self::CASH_IN, self::CASH_OUT => 0,
            self::LIPA_NAMBA_SALE, self::LIPA_NAMBA_SETTLEMENT => 0,
        };
    }
    // Je aina hii ni ya marekebisho?
    public function isAdjustment(): bool
    {
        return in_array($this, [
            self::CASH_IN,
            self::CASH_OUT,
            self::FLOAT_IN,
            self::FLOAT_OUT,
        ]);
    }
    // Je aina hii ni ya Lipa Namba?
    public function isLipaNamba(): bool
    {
        return in_array($this, [
            self::LIPA_NAMBA_SALE,
            self::LIPA_NAMBA_SETTLEMENT,
        ]);
    }
}
