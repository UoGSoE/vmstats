<?php

use App\Livewire\ApiKeyList;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Livewire;

test('only authenticated users can see the api keys page', function () {
    $response = $this->get(route('api_key.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can see the api keys page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('api_key.index'));

    $response->assertOk();
    $response->assertSee('API Keys');
    $response->assertSeeLivewire('api-key-list');
});

test('the api keys page lists existing tokens with their owner', function () {
    $alice = User::factory()->create(['username' => 'alice']);
    $bob = User::factory()->create(['username' => 'bob']);
    $alice->createToken('alices-laptop');
    $bob->createToken('bobs-kvm-host');

    $response = $this->actingAs($alice)->get(route('api_key.index'));

    $response->assertOk();
    $response->assertSee('alices-laptop');
    $response->assertSee('bobs-kvm-host');
    $response->assertSee('alice');
    $response->assertSee('bob');
});

test('a user can create a token for any user via the form', function () {
    $alice = User::factory()->create(['username' => 'alice']);
    $bob = User::factory()->create(['username' => 'bob']);

    Livewire::actingAs($alice)
        ->test(ApiKeyList::class)
        ->set('selectedUserId', $bob->id)
        ->set('tokenName', 'bobs-new-server')
        ->call('createToken');

    expect(PersonalAccessToken::count())->toBe(1);
    $token = PersonalAccessToken::first();
    expect($token->name)->toBe('bobs-new-server');
    expect($token->tokenable_id)->toBe($bob->id);
});

test('the plain-text token is shown once after creation and can be dismissed', function () {
    $alice = User::factory()->create();

    $component = Livewire::actingAs($alice)
        ->test(ApiKeyList::class)
        ->set('selectedUserId', $alice->id)
        ->set('tokenName', 'my-laptop')
        ->call('createToken');

    $plainText = $component->get('plainTextToken');
    expect($plainText)->not->toBeNull();
    expect($plainText)->toContain('|');
    $component->assertSee($plainText);

    $component->call('dismissPlainTextToken');
    expect($component->get('plainTextToken'))->toBeNull();
});

test('a token can be deleted permanently', function () {
    $alice = User::factory()->create();
    $token = $alice->createToken('to-be-deleted')->accessToken;

    Livewire::actingAs($alice)
        ->test(ApiKeyList::class)
        ->call('deleteToken', $token->id);

    expect(PersonalAccessToken::count())->toBe(0);
});

test('creating a token requires a name', function () {
    $alice = User::factory()->create();

    Livewire::actingAs($alice)
        ->test(ApiKeyList::class)
        ->set('selectedUserId', $alice->id)
        ->set('tokenName', '')
        ->call('createToken')
        ->assertHasErrors(['tokenName' => 'required']);

    expect(PersonalAccessToken::count())->toBe(0);
});

test('a token can be transferred to another user preserving the hash', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $newToken = $alice->createToken('shared-host');
    $token = $newToken->accessToken;
    $originalHash = $token->token;

    Livewire::actingAs($alice)
        ->test(ApiKeyList::class)
        ->call('transferToken', $token->id, $bob->id);

    $token->refresh();
    expect($token->tokenable_id)->toBe($bob->id);
    expect($token->token)->toBe($originalHash);
});
