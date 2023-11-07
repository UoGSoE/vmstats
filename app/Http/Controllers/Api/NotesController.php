<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Server;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function updateServer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2048',
            'notes_b64' => 'nullable|string|max:2048',
        ]);

        if (array_key_exists('notes_b64', $data) && $data['notes_b64']) {
            $data['notes'] = trim(base64_decode($data['notes_b64']));
        }

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
            'notes_b64' => 'nullable|string|max:2048',
        ]);

        if (array_key_exists('notes_b64', $data) && $data['notes_b64']) {
            $data['notes'] = trim(base64_decode($data['notes_b64']));
        }

        $guest = Guest::where('name', '=', $data['name'])->firstOrFail();
        $guest->update(['notes' => $data['notes'] ?? null]);

        return response()->json([
            'message' => 'VM guest notes updated',
        ], 200);
    }
}
