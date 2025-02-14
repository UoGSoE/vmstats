<?php

use App\Models\Guest;
use App\Models\Server;

test('we can store an incoming vm guest request', function () {
    $response = $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'guest' => 'Test Guest',
    ]);

    $response->assertOk();
    expect(Server::count())->toEqual(1);
    expect(Guest::count())->toEqual(1);
    $server = Server::first();
    $guest = Guest::first();
    expect($server->name)->toEqual('Test Server');
    expect($guest->name)->toEqual('Test Guest');
    expect($guest->server_id)->toEqual($server->id);
    expect($server->guests()->first()->is($guest))->toBeTrue();
});

test('we can store an incoming vm guest request with optional notes for the guest and server', function () {
    $response = $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'server_notes' => 'Blah blah blah',
        'guest' => 'Test Guest',
        'guest_notes' => 'Tum te tum',
    ]);

    $response->assertOk();
    expect(Server::count())->toEqual(1);
    expect(Guest::count())->toEqual(1);
    $server = Server::first();
    $guest = Guest::first();
    expect($server->name)->toEqual('Test Server');
    expect($server->notes)->toEqual('Blah blah blah');
    expect($guest->name)->toEqual('Test Guest');
    expect($guest->notes)->toEqual('Tum te tum');
    expect($guest->server_id)->toEqual($server->id);
    expect($server->guests()->first()->is($guest))->toBeTrue();
});

test('we can store an incoming vm guest request with optional notes which can be in base64', function () {
    $response = $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'server_notes_b64' => 'aSBhbSBhIGJhc2U2NCBzZXJ2ZXIK',
        'guest' => 'Test Guest',
        'guest_notes_b64' => 'aSBhbSBhIGJhc2U2NCBndWVzdAo=',
    ]);

    $response->assertOk();
    expect(Server::count())->toEqual(1);
    expect(Guest::count())->toEqual(1);
    $server = Server::first();
    $guest = Guest::first();
    expect($server->name)->toEqual('Test Server');
    expect($server->notes)->toEqual('i am a base64 server');
    expect($guest->name)->toEqual('Test Guest');
    expect($guest->notes)->toEqual('i am a base64 guest');
    expect($guest->server_id)->toEqual($server->id);
    expect($server->guests()->first()->is($guest))->toBeTrue();
});

test('we can update just the notes for a server or guest', function () {
    $server = Server::factory()->create(['notes' => 'Test Server']);
    $guest = Guest::factory()->create(['notes' => 'Test Guest']);

    $response = $this->postJson(route('api.guest.notes'), [
        'name' => $guest->name,
        'notes' => 'Tum te tum',
    ]);

    $response->assertOk();
    expect($guest->fresh()->notes)->toEqual('Tum te tum');

    $response = $this->postJson(route('api.server.notes'), [
        'name' => $server->name,
        'notes' => 'La de dah',
    ]);

    $response->assertOk();
    expect($server->fresh()->notes)->toEqual('La de dah');
});

test('we can update just the notes for a server or guest optionally in base64', function () {
    $server = Server::factory()->create(['notes' => 'Test Server']);
    $guest = Guest::factory()->create(['notes' => 'Test Guest']);

    $response = $this->postJson(route('api.guest.notes'), [
        'name' => $guest->name,
        'notes_b64' => 'aSBhbSBhIGJhc2U2NCBndWVzdAo=',
    ]);

    $response->assertOk();
    expect($guest->fresh()->notes)->toEqual('i am a base64 guest');

    $response = $this->postJson(route('api.server.notes'), [
        'name' => $server->name,
        'notes_b64' => 'aSBhbSBhIGJhc2U2NCBzZXJ2ZXIK',
    ]);

    $response->assertOk();
    expect($server->fresh()->notes)->toEqual('i am a base64 server');
});

test('storing the same request twice doesnt duplicate the data', function () {
    $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'guest' => 'Test Guest',
    ]);

    $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'guest' => 'Test Guest',
    ]);

    expect(Server::count())->toEqual(1);
    expect(Guest::count())->toEqual(1);
});

test('changing the vm guests server correctly updates the records', function () {
    $server = Server::factory()->create(['name' => 'Original Server']);
    $guest = Guest::factory()->create(['name' => 'Test Guest', 'server_id' => $server->id]);

    $this->postJson(route('api.vm.store'), [
        'server' => 'New Server',
        'guest' => 'Test Guest',
    ]);

    expect(Server::count())->toEqual(2);
    expect(Guest::count())->toEqual(1);
    expect($server->guests()->count())->toEqual(0);
    $newServer = Server::where('name', '=', 'New Server')->first();
    expect($newServer->guests()->count())->toEqual(1);
    expect($newServer->guests()->first()->is($guest))->toBeTrue();
});

test('we can delete a vm server', function () {
    $server = Server::factory()->create(['name' => 'Test Server 1']);
    $otherServer = Server::factory()->create(['name' => 'Test Server 2']);
    $guest = Guest::factory()->create(['server_id' => $server->id]);
    $otherGuest = Guest::factory()->create(['server_id' => $otherServer->id]);

    expect(Server::count())->toEqual(2);
    expect(Guest::count())->toEqual(2);

    $this->postJson(route('api.server.delete'), [
        'name' => 'Test Server 1',
    ]);

    expect(Server::count())->toEqual(1);
    expect(Guest::count())->toEqual(1);
    $this->assertDatabaseHas('servers', ['name' => $otherServer->name]);
    $this->assertDatabaseHas('guests', ['name' => $otherGuest->name]);
});

test('we can delete a vm guest', function () {
    $server = Server::factory()->create();
    $otherServer = Server::factory()->create();
    $guest = Guest::factory()->create(['server_id' => $server->id]);
    $otherGuest = Guest::factory()->create(['server_id' => $server->id]);

    expect(Server::count())->toEqual(2);
    expect(Guest::count())->toEqual(2);

    $this->postJson(route('api.guest.delete'), [
        'name' => $guest->name,
    ]);

    expect(Server::count())->toEqual(2);
    expect(Guest::count())->toEqual(1);
    expect($server->guests()->count())->toEqual(1);
    expect($server->guests()->first()->is($otherGuest))->toBeTrue();
});

test('we can get a list of servers and their guests', function () {
    $server = Server::factory()->create(['name' => 'aaa']);
    $otherServer = Server::factory()->create(['name' => 'bbb']);
    $guest = Guest::factory()->create(['server_id' => $server->id]);
    $otherGuest = Guest::factory()->create(['server_id' => $server->id]);

    $response = $this->getJson(route('api.server.index'));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
    $response->assertJson([
        'data' => [
            [
                'name' => $server->name,
                'guests' => [
                    [
                        'name' => $guest->name,
                    ],
                    [
                        'name' => $otherGuest->name,
                    ],
                ],
            ],
            [
                'name' => $otherServer->name,
                'guests' => [],
            ],
        ],
    ]);
});
