<?php

namespace App\Http\Livewire;

use App\Models\Guest;
use App\Models\Server;
use Livewire\Component;

class VmList extends Component
{
    public $currentNotes = null;
    public $currentName = null;

    public function render()
    {
        return view('livewire.vm-list', [
            'servers' => Server::orderBy('name')->with(['guests' => fn ($query) => $query->orderBy('name')])->get(),
        ]);
    }

    public function setCurrentNotes(int $guestId)
    {
        $guest = Guest::findOrFail($guestId);
        $this->currentName = $guest->name;
        $this->currentNotes = nl2br(e($guest->notes));
    }

    public function deleteServer(int $serverId)
    {
        $server = Server::findOrFail($serverId);
        $server->delete();
    }

    public function deleteGuest(int $guestId)
    {
        $guest = Guest::findOrFail($guestId);
        $guest->delete();
    }
}
