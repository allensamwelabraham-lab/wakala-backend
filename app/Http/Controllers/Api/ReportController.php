<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $user = $request->user()->business;

        // Kipindi: siku, wiki, mwezi, mwaka (chaguo-msingi: siku)
        $period = $request->string('period', 'siku')->toString();

        // Amua tarehe ya kuanzia
        $from = match ($period) {
            'wiki'  => now()->subDays(6)->startOfDay(),
            'mwezi' => now()->subDays(29)->startOfDay(),
            'mwaka' => now()->startOfYear(),
            default => now()->startOfDay(),   // siku
        };

        // Miamala ya kipindi hiki
        $transactions = $user->transactions()
            ->where('created_at', '>=', $from)
            ->get();

        // Muhtasari kwa kila aina
        $byType = [];
        foreach (TransactionType::cases() as $type) {
            $group = $transactions->where('type', $type);
            if ($group->count() > 0) {
                $byType[] = [
                    'type'   => $type->value,
                    'label'  => $type->label(),
                    'count'  => $group->count(),
                    'amount' => (float) $group->sum('amount'),
                ];
            }
        }

        // Data ya mwenendo (kwa graph) — kundi kwa tarehe
        $trend = $transactions
            ->groupBy(fn ($t) => $t->created_at->toDateString())
            ->map(fn ($group, $date) => [
                'date'       => $date,
                'count'      => $group->count(),
                'commission' => (float) $group->sum('commission'),
            ])
            ->sortBy('date')
            ->values();

        return response()->json([
            'period'           => $period,
            'total_commission' => (float) $transactions->sum('commission'),
            'total_count'      => $transactions->count(),
            'by_type'          => $byType,
            'trend'            => $trend,
        ]);
    }
}
