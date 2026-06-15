<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $service,
    ) {}

    public function index(Request $request)
    {
        $business = $request->user()->business;

        // Sorting salama — ruhusu safu hizi tu
        $sortBy  = in_array($request->string('sort_by'), ['created_at', 'amount', 'commission'])
            ? $request->string('sort_by')->toString()
            : 'created_at';
        $sortDir = $request->string('sort_dir') === 'asc' ? 'asc' : 'desc';

        $transactions = $business->transactions()
            ->with(['network', 'recorder'])
            ->when($request->filled('types'), function ($query) use ($request) {
                $query->whereIn('type', explode(',', $request->string('types')));
            })
            ->when($request->filled('network_id'), function ($query) use ($request) {
                $query->where('network_id', $request->integer('network_id'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date('date_to'));
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($request->integer('per_page', 15));

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'network_id' => ['nullable', 'exists:networks,id'],
            'type'       => ['required', Rule::enum(TransactionType::class)],
            'amount'     => ['required', 'numeric', 'min:1'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        $business = $request->user()->business;
        $type = TransactionType::from($data['type']);

        // Mtandao — tu kama umetumwa (lazima uwe wa biashara hii)
        $network = isset($data['network_id'])
            ? $business->networks()->findOrFail($data['network_id'])
            : null;

        // Note ya otomatiki kwa marekebisho
        $note = $data['note'] ?? null;
        if ($type->isAdjustment() && empty($note)) {
            $note = $type->label();
        }

        if ($type->isLipaNamba()) {
            $transaction = $this->service->recordLipaNamba(
                business:   $business,
                recorder:   $request->user(),
                type:       $type,
                amount:     (float) $data['amount'],
                commission: (float) ($data['commission'] ?? 0),
                note:       $note,
            );
        } else {
            $transaction = $this->service->record(
                business:   $business,
                recorder:   $request->user(),
                type:       $type,
                amount:     (float) $data['amount'],
                network:    $network,
                commission: (float) ($data['commission'] ?? 0),
                note:       $note,
            );
        }

        return response()->json($transaction->load(['network', 'recorder']), 201);
    }

    public function update(Request $request, Transaction $transaction)
    {
        // Muamala lazima uwe wa biashara ya mtumiaji
        abort_if($transaction->business_id !== $request->user()->business_id, 403);

        $data = $request->validate([
            'type'       => ['required', Rule::enum(TransactionType::class)],
            'amount'     => ['required', 'numeric', 'min:1'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        if ($transaction->type->isLipaNamba()) {
            $transaction = $this->service->updateLipaNamba(
                transaction: $transaction,
                amount:      (float) $data['amount'],
                commission:  (float) ($data['commission'] ?? 0),
                note:        $data['note'] ?? null,
            );
        } else {
            $transaction = $this->service->update(
                transaction: $transaction,
                type:        TransactionType::from($data['type']),
                amount:      (float) $data['amount'],
                commission:  (float) ($data['commission'] ?? 0),
                note:        $data['note'] ?? null,
            );
        }

        return response()->json($transaction->load(['network', 'recorder']));
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        // Muamala lazima uwe wa biashara ya mtumiaji
        abort_if($transaction->business_id !== $request->user()->business_id, 403);

        // Kufuta ni kwa BOSS tu
        abort_if(! $request->user()->isBoss(), 403, 'Boss pekee ndiye anaweza kufuta muamala.');

        $this->service->reverse($transaction);

        return response()->json(['message' => 'Muamala umefutwa.']);
    }
}
