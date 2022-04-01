<?php

namespace App\Http\Controllers\Api;

use App\Models\Guest;
use App\Models\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotesController extends Controller
{
    public function updateServer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2048',
        ]);

        $server = Server::where('name', '=', $data['name'])->firstOrFail();
        $server->update(['notes' => $data['notes'] ?? null]);

        return response()->json([
            'message' => 'VM server notes updated',
        ], 200);
    }

    public function updateGuest(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2048',
        ]);

        $guest = Guest::where('name', '=', $data['name'])->firstOrFail();
        $guest->update(['notes' => $data['notes'] ?? null]);

        return response()->json([
            'message' => 'VM guest notes updated',
        ], 200);
    }
}
