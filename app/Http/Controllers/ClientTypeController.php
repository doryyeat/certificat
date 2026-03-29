<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientTypeController extends Controller
{
    public function setType(Request $request)
    {
        $validated = $request->validate([
            'clientType' => 'required|in:client,business'
        ]);

        return response()->json(['ok' => true])
            ->cookie('client_type', $validated['clientType'], 60 * 24 * 30); // 30 дней
    }
}
