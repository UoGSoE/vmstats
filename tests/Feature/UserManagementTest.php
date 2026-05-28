<?php

use App\Livewire\UserList;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Livewire;
use Ohffs\Ldap\FakeLdapConnection;
use Ohffs\Ldap\LdapConnectionInterface;
use Ohffs\Ldap\LdapUser;

test('only authenticated users can see the user management page', function () {
    $response = $this->get(route('user.index'));

    $response->assertRedirect(route('login'));
});

test('existing users can see the user management page', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $response = $this->actingAs($user1)->get(route('user.index'));

    $response->assertOk();
    $response->assertSee('User Management');
    $response->assertSee($user1->username);
    $response->assertSee($user2->username);
    $response->assertSeeLivewire('user-list');
});

test('users can delete an existing user but not themselves', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Livewire::actingAs($user1)
        ->test(UserList::class)
        ->assertSee($user1->username)
        ->assertSee($user2->username)
        ->call('confirmDeleteUser', $user2->id)
        ->assertSee($user1->username)
        ->assertDontSee($user2->username)
        ->call('confirmDeleteUser', $user1->id)
        ->assertSee($user1->username)
        ->assertDontSee($user2->username);
});

test('deleting a user also removes their api tokens', function () {
    $user1 = User::factory()->create();
    $doomed = User::factory()->create();
    $doomed->createToken('soon-to-be-gone');

    expect(PersonalAccessToken::count())->toBe(1);

    Livewire::actingAs($user1)
        ->test(UserList::class)
        ->call('confirmDeleteUser', $doomed->id);

    expect(User::find($doomed->id))->toBeNull();
    expect(PersonalAccessToken::count())->toBe(0);
});

test('a leaving user can have their tokens transferred to another user before deletion', function () {
    $admin = User::factory()->create();
    $leaver = User::factory()->create();
    $heir = User::factory()->create();
    $token = $leaver->createToken('field-kvm-host-7')->accessToken;
    $originalHash = $token->token;

    Livewire::actingAs($admin)
        ->test(UserList::class)
        ->call('transferTokensAndDeleteUser', $leaver->id, $heir->id);

    expect(User::find($leaver->id))->toBeNull();
    expect(PersonalAccessToken::count())->toBe(1);
    $token->refresh();
    expect($token->tokenable_id)->toBe($heir->id);
    expect($token->token)->toBe($originalHash);
});

test('users can add a new ldap user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    fakeLdapConnection();
    \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
        [
            'uid' => ['abc1x'],
            'mail' => ['abc1x@example.com'],
            'sn' => ['smith'],
            'givenname' => ['jenny'],
            'telephonenumber' => ['12345'],
        ],
    ]));

    Livewire::actingAs($user1)
        ->test(UserList::class)
        ->assertSee($user1->username)
        ->assertSee($user2->username)
        ->assertDontSee('abc1x@example.com')
        ->set('username', 'abc1x')
        ->call('lookupUser')
        ->assertSet('email', 'abc1x@example.com')
        ->call('createUser')
        ->assertSee('abc1x@example.com')
        ->assertSet('email', '');

    $this->assertDatabaseHas('users', [
        'username' => 'abc1x',
        'surname' => 'smith',
        'forenames' => 'jenny',
        'email' => 'abc1x@example.com',
    ]);
});

test('users cant add a user that doesnt exist in ldap', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    fakeLdapConnection();
    \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(false);

    Livewire::actingAs($user1)
        ->test(UserList::class)
        ->assertSee($user1->username)
        ->assertSee($user2->username)
        ->assertDontSee('abc1x@example.com')
        ->set('username', 'abc1x')
        ->call('lookupUser')
        ->assertSet('email', '')
        ->assertSet('error', 'User not found')
        ->call('createUser');

    $this->assertDatabaseMissing('users', [
        'username' => 'abc1x',
    ]);
});

test('users cant add the same user twice', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create(['username' => 'abc1x']);
    fakeLdapConnection();
    \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
        [
            'uid' => ['abc1x'],
            'mail' => ['abc1x@example.com'],
            'sn' => ['smith'],
            'givenname' => ['jenny'],
            'telephonenumber' => ['12345'],
        ],
    ]));

    Livewire::actingAs($user1)
        ->test(UserList::class)
        ->assertSee($user1->username)
        ->assertSee($user2->username)
        ->set('username', 'abc1x')
        ->call('lookupUser')
        ->assertSet('email', '')
        ->assertSet('error', 'User already exists')
        ->call('createUser');

    expect(User::where('username', '=', 'abc1x')->count())->toEqual(1);
});

// Helpers
function fakeLdapConnection()
{
    test()->instance(
        LdapConnectionInterface::class,
        new FakeLdapConnection('up', 'whatever')
    );
}
