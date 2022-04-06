<?php

namespace App\Http\Livewire;

use App\Models\Guest;
use App\Models\Server;
use Livewire\Component;

class VmList extends Component
{
    public $currentNotes = null;
    public $currentName = null;
    public $filter = null;

    public function render()
    {
        return view('livewire.vm-list', [
            'servers' => $this->getFilteredVmList(),
        ]);
    }

    public function getFilteredVmList()
    {
        $servers = Server::orderBy('name')->with([
            'guests' => fn ($query) => $query->orderBy('name')
                            ->when(
                                $this->filter,
                                fn ($query) => $query->where('name', 'like', '%' . $this->filter . '%')
                                                ->orWhere('notes', 'like', '%' . $this->filter . '%')
                            )
        ])->get();

        if ($this->filter) {
            $servers = $servers->map(function ($server) {
                $server->guests = $server->guests->map(function ($guest) {
                    $guest->notes_filter_match = str_contains($guest->notes, $this->filter);
                    return $guest;
                });
                return $server;
            });
        }

        return $servers;
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
