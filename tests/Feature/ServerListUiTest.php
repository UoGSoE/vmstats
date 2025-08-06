<?php

use App\Models\Guest;
use App\Models\Server;
use App\Models\User;
use Livewire\Livewire;

test('unauthenticated users cant see the server list', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('we can see the main page with the livewire server list component', function () {
    $user = User::factory()->create();
    $server1 = Server::factory()->create(['name' => 'Server 1']);
    $server2 = Server::factory()->create(['name' => 'Server 2']);
    $guest1 = Guest::factory()->create(['name' => 'Guest 1', 'server_id' => $server1->id]);
    $guest2 = Guest::factory()->create(['name' => 'Guest 2', 'server_id' => $server2->id]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertSeeLivewire('vm-list');
    $response->assertSee('Server 1');
    $response->assertSee('Server 2');
    $response->assertSee('Guest 1');
    $response->assertSee('Guest 2');
});

test('we can filter the list of vms and servers', function () {
    $server1 = Server::factory()->create(['name' => 'Server 1']);
    $server2 = Server::factory()->create(['name' => 'Server 2']);
    $guest1 = Guest::factory()->create(['name' => 'Guest 1', 'server_id' => $server1->id]);
    $guest2 = Guest::factory()->create(['name' => 'Guest 2', 'server_id' => $server2->id]);
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('vm-list')
        ->assertSee('Server 1')
        ->assertSee('Server 2')
        ->assertSee('Guest 1')
        ->assertSee('Guest 2')
        // we don't filter on server names
        ->set('filter', 'Server 1')
        ->assertSee('Server 1')
        ->assertSee('Server 2')
        ->assertDontSee('Guest 1')
        ->assertDontSee('Guest 2')
        // filter on vm name still shows the server it is running on
        ->set('filter', 'Guest 1')
        ->assertSee('Server 1')
        ->assertSee('Guest 1')
        ->assertSee('Server 2')
        ->assertDontSee('Guest 2')
        ->set('filter', 'Guest 2')
        ->assertSee('Server 1')
        ->assertDontSee('Guest 1')
        ->assertSee('Server 2')
        ->assertSee('Guest 2');
});
