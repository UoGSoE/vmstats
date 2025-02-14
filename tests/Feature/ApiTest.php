<?php

use App\Models\Guest;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('we can store an incoming vm guest request', function () {
    $response = $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'guest' => 'Test Guest',
    ]);

    $response->assertOk();
    $this->assertEquals(1, Server::count());
    $this->assertEquals(1, Guest::count());
    $server = Server::first();
    $guest = Guest::first();
    $this->assertEquals('Test Server', $server->name);
    $this->assertEquals('Test Guest', $guest->name);
    $this->assertEquals($server->id, $guest->server_id);
    $this->assertTrue($server->guests()->first()->is($guest));
});

test('we can store an incoming vm guest request with optional notes for the guest and server', function () {
    $response = $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'server_notes' => 'Blah blah blah',
        'guest' => 'Test Guest',
        'guest_notes' => 'Tum te tum',
    ]);

    $response->assertOk();
    $this->assertEquals(1, Server::count());
    $this->assertEquals(1, Guest::count());
    $server = Server::first();
    $guest = Guest::first();
    $this->assertEquals('Test Server', $server->name);
    $this->assertEquals('Blah blah blah', $server->notes);
    $this->assertEquals('Test Guest', $guest->name);
    $this->assertEquals('Tum te tum', $guest->notes);
    $this->assertEquals($server->id, $guest->server_id);
    $this->assertTrue($server->guests()->first()->is($guest));
});

test('we can store an incoming vm guest request with optional notes which can be in base64', function () {
    $response = $this->postJson(route('api.vm.store'), [
        'server' => 'Test Server',
        'server_notes_b64' => 'aSBhbSBhIGJhc2U2NCBzZXJ2ZXIK',
        'guest' => 'Test Guest',
        'guest_notes_b64' => 'aSBhbSBhIGJhc2U2NCBndWVzdAo=',
    ]);

    $response->assertOk();
    $this->assertEquals(1, Server::count());
    $this->assertEquals(1, Guest::count());
    $server = Server::first();
    $guest = Guest::first();
    $this->assertEquals('Test Server', $server->name);
    $this->assertEquals('i am a base64 server', $server->notes);
    $this->assertEquals('Test Guest', $guest->name);
    $this->assertEquals('i am a base64 guest', $guest->notes);
    $this->assertEquals($server->id, $guest->server_id);
    $this->assertTrue($server->guests()->first()->is($guest));
});

test('we can update just the notes for a server or guest', function () {
    $server = Server::factory()->create(['notes' => 'Test Server']);
    $guest = Guest::factory()->create(['notes' => 'Test Guest']);

    $response = $this->postJson(route('api.guest.notes'), [
        'name' => $guest->name,
        'notes' => 'Tum te tum',
    ]);

    $response->assertOk();
    $this->assertEquals('Tum te tum', $guest->fresh()->notes);

    $response = $this->postJson(route('api.server.notes'), [
        'name' => $server->name,
        'notes' => 'La de dah',
    ]);

    $response->assertOk();
    $this->assertEquals('La de dah', $server->fresh()->notes);
});

test('we can update just the notes for a server or guest optionally in base64', function () {
    $server = Server::factory()->create(['notes' => 'Test Server']);
    $guest = Guest::factory()->create(['notes' => 'Test Guest']);

    $response = $this->postJson(route('api.guest.notes'), [
        'name' => $guest->name,
        'notes_b64' => 'aSBhbSBhIGJhc2U2NCBndWVzdAo=',
    ]);

    $response->assertOk();
    $this->assertEquals('i am a base64 guest', $guest->fresh()->notes);

    $response = $this->postJson(route('api.server.notes'), [
        'name' => $server->name,
        'notes_b64' => 'aSBhbSBhIGJhc2U2NCBzZXJ2ZXIK',
    ]);

    $response->assertOk();
    $this->assertEquals('i am a base64 server', $server->fresh()->notes);
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

    $this->assertEquals(1, Server::count());
    $this->assertEquals(1, Guest::count());
});

test('changing the vm guests server correctly updates the records', function () {
    $server = Server::factory()->create(['name' => 'Original Server']);
    $guest = Guest::factory()->create(['name' => 'Test Guest', 'server_id' => $server->id]);

    $this->postJson(route('api.vm.store'), [
        'server' => 'New Server',
        'guest' => 'Test Guest',
    ]);

    $this->assertEquals(2, Server::count());
    $this->assertEquals(1, Guest::count());
    $this->assertEquals(0, $server->guests()->count());
    $newServer = Server::where('name', '=', 'New Server')->first();
    $this->assertEquals(1, $newServer->guests()->count());
    $this->assertTrue($newServer->guests()->first()->is($guest));
});

test('we can delete a vm server', function () {
    $server = Server::factory()->create(['name' => 'Test Server 1']);
    $otherServer = Server::factory()->create(['name' => 'Test Server 2']);
    $guest = Guest::factory()->create(['server_id' => $server->id]);
    $otherGuest = Guest::factory()->create(['server_id' => $otherServer->id]);

    $this->assertEquals(2, Server::count());
    $this->assertEquals(2, Guest::count());

    $this->postJson(route('api.server.delete'), [
        'name' => 'Test Server 1',
    ]);

    $this->assertEquals(1, Server::count());
    $this->assertEquals(1, Guest::count());
    $this->assertDatabaseHas('servers', ['name' => $otherServer->name]);
    $this->assertDatabaseHas('guests', ['name' => $otherGuest->name]);
});

test('we can delete a vm guest', function () {
    $server = Server::factory()->create();
    $otherServer = Server::factory()->create();
    $guest = Guest::factory()->create(['server_id' => $server->id]);
    $otherGuest = Guest::factory()->create(['server_id' => $server->id]);

    $this->assertEquals(2, Server::count());
    $this->assertEquals(2, Guest::count());

    $this->postJson(route('api.guest.delete'), [
        'name' => $guest->name,
    ]);

    $this->assertEquals(2, Server::count());
    $this->assertEquals(1, Guest::count());
    $this->assertEquals(1, $server->guests()->count());
    $this->assertTrue($server->guests()->first()->is($otherGuest));
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
