<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Network;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->business->networks()->orderBy('id')->get()
        );
    }

    public function store(Request $request)
    {
        // Kuongeza mtandao ni kwa boss tu
        abort_if(! $request->user()->isBoss(), 403, 'Boss pekee ndiye anaweza kuongeza mtandao.');

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'color'         => ['required', 'string', 'max:20'],
            'float_balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $network = $request->user()->business->networks()->create([
            'name'          => $data['name'],
            'color'         => $data['color'],
            'float_balance' => $data['float_balance'] ?? 0,
        ]);

        return response()->json($network, 201);
    }

    public function update(Request $request, Network $network)
    {
        abort_if($network->business_id !== $request->user()->business_id, 403);
        abort_if(! $request->user()->isBoss(), 403, 'Boss pekee ndiye anaweza kuhariri mtandao.');

        $data = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:20'],
        ]);

        $network->update($data);

        return response()->json($network);
    }
}
