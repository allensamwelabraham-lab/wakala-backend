<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->user()->business;

        $todayTxns = $business->transactions()->whereDate('created_at', today());

        return response()->json([
            'business_name'      => $business->name,
            'cash_balance'       => (float) $business->cash_balance,
            'lipa_namba_balance' => (float) $business->lipa_namba_balance,
            'total_float'        => $business->totalFloat(),
            'commission_today'   => (float) (clone $todayTxns)->sum('commission'),
            'transactions_today' => (clone $todayTxns)->count(),
            'networks'           => $business->networks()->orderBy('id')->get(),
        ]);
    }
}
