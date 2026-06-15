<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // Orodha ya wafanyakazi wa biashara (boss tu)
    public function index(Request $request)
    {
        abort_if(! $request->user()->isBoss(), 403, 'Boss pekee ndiye anaweza.');

        $employees = $request->user()->business
            ->users()
            ->where('role', 'mfanyakazi')
            ->get();

        return response()->json($employees);
    }

    // Sajili mfanyakazi mpya (boss tu)
    public function store(Request $request)
    {
        abort_if(! $request->user()->isBoss(), 403, 'Boss pekee ndiye anaweza kusajili wafanyakazi.');

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $employee = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'business_id' => $request->user()->business_id,
            'role'        => 'mfanyakazi',
        ]);

        return response()->json($employee, 201);
    }

    // Ondoa mfanyakazi (boss tu)
    public function destroy(Request $request, User $employee)
    {
        abort_if(! $request->user()->isBoss(), 403, 'Boss pekee ndiye anaweza.');

        // Hakikisha mfanyakazi ni wa biashara ya boss huyu
        abort_if($employee->business_id !== $request->user()->business_id, 403);

        // Boss hawezi kujifuta mwenyewe
        abort_if($employee->id === $request->user()->id, 403, 'Huwezi kujifuta mwenyewe.');

        $employee->delete();

        return response()->json(['message' => 'Mfanyakazi ameondolewa.']);
    }
}
