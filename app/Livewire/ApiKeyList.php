<?php

namespace App\Livewire;

use App\Models\User;
use Flux\Flux;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Component;

class ApiKeyList extends Component
{
    public ?int $selectedUserId = null;

    public string $tokenName = '';

    public ?string $plainTextToken = null;

    /** @var array<int, int|null> token id => target user id */
    public array $transferTargets = [];

    public function mount(): void
    {
        $this->selectedUserId = auth()->id();
    }

    public function createToken(): void
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'tokenName' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $newToken = $user->createToken($this->tokenName);

        $this->plainTextToken = $newToken->plainTextToken;

        $this->reset(['tokenName']);
        $this->selectedUserId = auth()->id();
    }

    public function dismissPlainTextToken(): void
    {
        $this->plainTextToken = null;
    }

    public function deleteToken(int $tokenId): void
    {
        PersonalAccessToken::findOrFail($tokenId)->delete();
    }

    public function transferToken(int $tokenId, ?int $targetUserId): void
    {
        if (! $targetUserId) {
            return;
        }

        $target = User::findOrFail($targetUserId);
        $token = PersonalAccessToken::findOrFail($tokenId);
        // tokenable_id sits outside Sanctum's $fillable, so set directly.
        $token->tokenable_id = $target->id;
        $token->save();

        unset($this->transferTargets[$tokenId]);
        Flux::modal("revoke-{$tokenId}")->close();
    }

    public function render()
    {
        return view('livewire.api-key-list', [
            'tokens' => PersonalAccessToken::with('tokenable')->orderBy('name')->get(),
            'users' => User::orderBy('username')->get(),
        ]);
    }
}
