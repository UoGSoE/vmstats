<?php

namespace App\Livewire;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;

class UserList extends Component
{
    public $username = '';

    public $surname = '';

    public $forenames = '';

    public $email = '';

    public $error = null;

    /** @var array<int, int|null> user id => target user id for token transfer */
    public array $transferTargets = [];

    public function render()
    {
        return view('livewire.user-list', [
            'users' => User::orderBy('surname')->withCount('tokens')->get(),
        ]);
    }

    public function lookupUser()
    {
        if (! $this->username) {
            return;
        }

        $existingUser = User::where('username', '=', $this->username)->first();
        if ($existingUser) {
            $this->error = 'User already exists';

            return;
        }

        $ldapUser = \Ldap::findUser($this->username);
        if (! $ldapUser) {
            $this->error = 'User not found';

            return;
        }

        $this->username = $ldapUser->username;
        $this->surname = $ldapUser->surname;
        $this->forenames = $ldapUser->forenames;
        $this->email = $ldapUser->email;
    }

    public function createUser()
    {
        $this->validate([
            'username' => 'required|unique:users',
            'surname' => 'required',
            'forenames' => 'required',
            'email' => 'required|email|unique:users',
        ]);

        User::create([
            'username' => $this->username,
            'surname' => $this->surname,
            'forenames' => $this->forenames,
            'email' => $this->email,
            'is_staff' => true,
            'password' => bcrypt(Str::random(64)),
        ]);

        $this->reset();
    }

    public function confirmDeleteUser($userId)
    {
        if (auth()->id() == $userId) {
            return;
        }
        $user = User::findOrFail($userId);
        // Cascade: kill any tokens the user owns. Sanctum doesn't FK-cascade.
        $user->tokens()->delete();
        $user->delete();
        Flux::modal("delete-user-{$userId}")->close();
    }

    public function transferTokensAndDeleteUser($userId, $targetUserId)
    {
        if (auth()->id() == $userId || ! $targetUserId || $userId == $targetUserId) {
            return;
        }
        $user = User::findOrFail($userId);
        $target = User::findOrFail($targetUserId);

        // Reparent each token. tokenable_id is outside Sanctum's $fillable
        // so we set it directly rather than via update().
        foreach ($user->tokens as $token) {
            $token->tokenable_id = $target->id;
            $token->save();
        }

        $user->delete();
        unset($this->transferTargets[$userId]);
        Flux::modal("delete-user-{$userId}")->close();
    }
}
