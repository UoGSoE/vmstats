<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Server;
use Illuminate\Http\Request;

class VmController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Server::orderBy('name')->with('guests')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'server' => 'required|string|max:255',
            'guest' => 'required|string|max:255',
            'server_notes' => 'nullable|string|max:2048',
            'guest_notes' => 'nullable|string|max:2048',
            'server_notes_b64' => 'nullable|string|max:2048',
            'guest_notes_b64' => 'nullable|string|max:2048',
        ]);

        if (array_key_exists('server_notes_b64', $data) && $data['server_notes_b64']) {
            $data['server_notes'] = trim(base64_decode($data['server_notes_b64']));
        }
        if (array_key_exists('guest_notes_b64', $data) && $data['guest_notes_b64']) {
            $data['guest_notes'] = trim(base64_decode($data['guest_notes_b64']));
        }

        $server = Server::firstOrCreate(['name' => $data['server']]);
        $server->update(['notes' => $data['server_notes'] ?? null]);
        $guest = Guest::firstOrCreate(['name' => $data['guest']]);
        $guest->update(['server_id' => $server->id, 'notes' => $data['guest_notes'] ?? null]);

        return response()->json([
            'message' => 'VM guest request stored',
        ], 200);
    }

    public function destroyServer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $server = Server::where('name', '=', $data['name'])->firstOrFail();
        $server->delete();

        return response()->json([
            'message' => 'VM server removed',
        ], 200);
    }

    public function destroyGuest(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $guest = Guest::where('name', '=', $data['name'])->firstOrFail();
        $guest->delete();

        return response()->json([
            'message' => 'VM guest removed',
        ], 200);
    }
}
